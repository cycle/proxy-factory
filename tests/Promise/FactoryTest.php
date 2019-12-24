<?php

/**
 * Spiral Framework. Cycle ProxyFactory
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */

declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests;

use Cycle\Annotated\Entities;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\Promise\Exception\ProxyFactoryException;
use Cycle\ORM\Promise\MaterializerInterface;
use Cycle\ORM\Promise\Materizalizer\EvalMaterializer;
use Cycle\ORM\Promise\Materizalizer\FileMaterializer;
use Cycle\ORM\Promise\PromiseInterface;
use Cycle\ORM\Promise\ProxyFactory;
use Cycle\ORM\Promise\Resolver;
use Cycle\ORM\Promise\Tests\Fixtures\SchematicEntity;
use Cycle\ORM\Transaction;
use Cycle\Schema;
use PDO;
use Spiral\Core\Container;
use Spiral\Database\Driver\SQLite\SQLiteDriver;
use Throwable;

class FactoryTest extends BaseTest
{
    public const DRIVER = 'sqlite';

    /** @var Container */
    private $container;

    public function setUp(): void
    {
        self::$config = [
            'debug'     => false,
            'strict'    => true,
            'benchmark' => false,
            'sqlite'    => [
                'driver' => SQLiteDriver::class,
                'check'  => static function () {
                    return !in_array('sqlite', PDO::getAvailableDrivers(), true);
                },
                'conn'   => 'sqlite::memory:',
                'user'   => 'sqlite',
                'pass'   => ''
            ],
        ];

        parent::setUp();

        $files = glob($this->filesDirectory() . DIRECTORY_SEPARATOR . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        $this->container = new Container();
        $this->container->bind(ORMInterface::class, $this->orm());
    }

    /**
     * @dataProvider dataProvider
     *
     * @param string $materializer
     * @param array  $params
     *
     * @throws ProxyFactoryException
     * @throws Throwable
     */
    public function testPromise(string $materializer, array $params): void
    {
        $role = SchematicEntity::class;
        $orm = $this->orm();
        $orm->make($role, ['id' => 1, 'name' => 'my name']);
        $orm->make($role, ['id' => 2, 'name' => 'my second name', 'email' => 'my email']);

        $tr = new Transaction($orm);
        $tr->run();

        $scope = ['id' => 2];

        $this->bindMaterializer($this->container->make($materializer, $params));

        /** @var SchematicEntity|Resolver $promise */
        $promise = $this->factory()->promise($orm, $role, $scope);

        $this->assertInstanceOf($role, $promise);
        $this->assertNotNull($promise->__resolve());

        $this->assertSame('my second name', $promise->getName());

        $promise->setName('my third name');
        $this->assertSame('my third name', $promise->getName());

        $promise->email = 'my second email';

        $tr = new Transaction($orm);
        $tr->persist($promise);
        $tr->run();

        /** @var SchematicEntity $o */
        $o = $orm->get($role, ['id' => 2]);
        $this->assertEquals('my third name', $o->getName());
        $this->assertEquals('my second email', $o->email);

        $cloned = clone $promise;
        $cloned->email = 'my cloned email';
        $this->assertNotEquals($cloned->email, $promise->email);
    }

    /**
     * @dataProvider dataProvider
     *
     * @param string $materializer
     * @param array  $params
     *
     * @throws ProxyFactoryException
     * @throws Throwable
     */
    public function testNullScope(string $materializer, array $params): void
    {
        $this->expectException(ProxyFactoryException::class);
        $this->expectExceptionMessageRegExp('/Method `\w+\(\)` not loaded for/');

        $role = SchematicEntity::class;
        $orm = $this->orm();
        $orm->make($role, ['id' => 1, 'name' => 'my name']);
        $orm->make($role, ['id' => 2, 'name' => 'my second name', 'email' => 'my email']);

        $scope = [];

        $this->bindMaterializer($this->container->make($materializer, $params));

        /** @var SchematicEntity|PromiseInterface $promise */
        $promise = $this->factory()->promise($orm, $role, $scope);

        $this->assertInstanceOf($role, $promise);
        $this->assertNull($promise->__resolve());

        $this->assertSame('my second name', $promise->getName());
    }

    /**
     * @dataProvider dataProvider
     *
     * @param string $materializer
     * @param array  $params
     *
     * @throws ProxyFactoryException
     * @throws Throwable
     */
    public function testUnknownScope(string $materializer, array $params): void
    {
        $this->expectException(ProxyFactoryException::class);
        $this->expectExceptionMessageRegExp('/Method `\w+\(\)` not loaded for/');

        $role = SchematicEntity::class;
        $orm = $this->orm();
        $orm->make($role, ['id' => 1, 'name' => 'my name']);
        $orm->make($role, ['id' => 2, 'name' => 'my second name', 'email' => 'my email']);

        $scope = ['id' => 3];

        $this->bindMaterializer($this->container->make($materializer, $params));

        /** @var SchematicEntity|PromiseInterface $promise */
        $promise = $this->factory()->promise($orm, $role, $scope);

        $this->assertInstanceOf($role, $promise);
        $this->assertNull($promise->__resolve());

        $this->assertSame('my second name', $promise->getName());
    }

    /**
     * @dataProvider dataProvider
     *
     * @param string $materializer
     * @param array  $params
     *
     * @throws ProxyFactoryException
     * @throws Throwable
     */
    public function testUnknownProperty(string $materializer, array $params): void
    {
        $this->expectException(ProxyFactoryException::class);
        $this->expectExceptionMessageRegExp('/Property `\w+` not loaded in `[_a-z]+\(\)` method for/');

        $role = SchematicEntity::class;
        $orm = $this->orm();
        $orm->make($role, ['id' => 1, 'name' => 'my name']);
        $orm->make($role, ['id' => 2, 'name' => 'my second name', 'email' => 'my email']);

        $scope = ['id' => 3];

        $this->bindMaterializer($this->container->make($materializer, $params));

        /** @var SchematicEntity|PromiseInterface $promise */
        $promise = $this->factory()->promise($orm, $role, $scope);

        $this->assertInstanceOf($role, $promise);

        $promise->email = 'example@test.com';
    }

    public function dataProvider(): array
    {
        return [
            [FileMaterializer::class, ['directory' => $this->filesDirectory()]],
            [EvalMaterializer::class, []]
        ];
    }

    private function filesDirectory(): string
    {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'promises';
    }

    private function orm(): ORMInterface
    {
        $schema = (new Schema\Compiler())->compile(new Schema\Registry($this->dbal), [
            new Entities($this->locator),
            new Schema\Generator\ResetTables(),
            new Schema\Generator\GenerateRelations(),
            new Schema\Generator\ValidateEntities(),
            new Schema\Generator\RenderTables(),
            new Schema\Generator\RenderRelations(),
            new Schema\Generator\SyncTables(),
            new Schema\Generator\GenerateTypecast(),
        ]);

        return $this->withSchema(new \Cycle\ORM\Schema($schema));
    }

    /**
     * @return ProxyFactory
     * @throws Throwable
     */
    private function factory(): ProxyFactory
    {
        return $this->container->get(ProxyFactory::class);
    }

    private function bindMaterializer(MaterializerInterface $materializer): void
    {
        $this->container->bind(MaterializerInterface::class, $materializer);
    }
}
