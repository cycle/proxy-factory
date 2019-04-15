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
                'check'  => function () {
                    return !in_array('sqlite', \PDO::getAvailableDrivers());
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

    public function testFilePromise(): void
    {
        $role = SchematicEntity::class;
        $scope = [];

        $this->bindMaterializer($this->container->make(FileMaterializer::class, ['directory' => $this->filesDirectory()]));

        /** @var SchematicEntity $promise */
        $promise = $this->factory()->promise($this->orm(), $role, $scope);

        $this->assertInstanceOf($role, $promise);

//        $promise->setName('my name');
//        $this->assertSame('my name', $promise->getName());
    }

    public function testEvalPromise(): void
    {
        $role = SchematicEntity::class;
        $scope = [];

        $this->bindMaterializer($this->container->get(EvalMaterializer::class));

        /** @var SchematicEntity $promise */
        $promise = $this->factory()->promise($this->orm(), $role, $scope);

        $this->assertInstanceOf($role, $promise);

//        $promise->setName('my name');
//        $this->assertSame('my name', $promise->getName());
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

    private function bindMaterializer(MaterializerInterface $materializer)
    {
        $this->container->bind(MaterializerInterface::class, $materializer);
    }
}