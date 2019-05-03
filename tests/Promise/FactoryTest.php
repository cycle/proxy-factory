<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests;

use Cycle\Annotated\Entities;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\Promise\Factory;
use Cycle\ORM\Promise\MaterializerInterface;
use Cycle\ORM\Promise\Materizalizer\EvalMaterializer;
use Cycle\ORM\Promise\Materizalizer\FileMaterializer;
use Cycle\ORM\Promise\Tests\Fixtures\SchematicEntity;
use Cycle\ORM\Transaction;
use Cycle\Schema;
use Spiral\Core\Container;
use Spiral\Database\Driver\SQLite\SQLiteDriver;

class FactoryTest extends BaseTest
{
    public const DRIVER = 'sqlite';

    /** @var \Spiral\Core\Container */
    private $container;

    public function setUp()
    {
        self::$config = [
            'debug'     => false,
            'strict'    => true,
            'benchmark' => false,
            'sqlite'    => [
                'driver' => SQLiteDriver::class,
                'check'  => static function () {
                    return !in_array('sqlite', \PDO::getAvailableDrivers(), true);
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

    private function filesDirectory(): string
    {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'promises';
    }

    /**
     * @dataProvider dataProvider
     *
     * @param string $materializer
     * @param array  $params
     *
     * @throws \Cycle\ORM\Promise\ProxyFactoryException
     * @throws \Throwable
     */
    public function testPromise(string $materializer, array $params): void
    {
        $role = SchematicEntity::class;
        $this->orm()->make($role, ['id' => 1, 'name' => 'my name']);
        $this->orm()->make($role, ['id' => 2, 'name' => 'my second name']);

        $scope = ['id' => 2];

        $this->bindMaterializer($this->container->make($materializer, $params));

        /** @var SchematicEntity $promise */
        $promise = $this->factory()->promise($this->orm(), $role, $scope);

        $this->assertInstanceOf($role, $promise);

        $this->assertSame('my second name', $promise->getName());

        $promise->setName('my third name');
        $this->assertSame('my third name', $promise->getName());

        $tr = new Transaction($this->orm());
        $tr->persist($promise);
        $tr->run();

        /** @var SchematicEntity $o */
        $o = $this->orm()->get($role, 'id', 2);
        $this->assertEquals('my third name', $o->getName());
    }

    public function dataProvider(): array
    {
        return [
            [FileMaterializer::class, ['directory' => $this->filesDirectory()]],
            [EvalMaterializer::class, []]
        ];
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

    private function factory(): Factory
    {
        return $this->container->get(Factory::class);
    }

    private function bindMaterializer(MaterializerInterface $materializer): void
    {
        $this->container->bind(MaterializerInterface::class, $materializer);
    }
}