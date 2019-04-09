<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\Promise\Factory;
use Cycle\ORM\Promise\MaterializerInterface;
use Cycle\ORM\Promise\Materizalizer\EvalMaterializer;
use Cycle\ORM\Promise\Materizalizer\FileMaterializer;
use Cycle\ORM\Promise\Tests\Fixtures\Entity;
use Cycle\ORM\SchemaInterface;
use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;

class FactoryTest extends TestCase
{
    private const NS = 'Cycle\ORM\Promise\Tests\Promises';

    public function setUp()
    {
        $files = glob($this->filesDirectory() . DIRECTORY_SEPARATOR . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    private function filesDirectory(): string
    {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'promises';
    }

    public function testFilePromise(): void
    {
        $role = Entity::class;

        $orm = \Mockery::mock(ORMInterface::class);
        $schema = \Mockery::mock(SchemaInterface::class);
        $schema->shouldReceive('define')->andReturn($role);
        $orm->shouldReceive('getSchema')->andReturn($schema);

        $container = new Container();
        $container->bind(ORMInterface::class, $orm);

        $materializer = $container->make(FileMaterializer::class, ['directory' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'promises']);
        $container->bind(MaterializerInterface::class, $materializer);

        /** @var Factory $factory */
        $factory = $container->get(Factory::class);

        /** @var Entity $promise */
        $promise = $factory->promise($orm, $role, []);

        $this->assertInstanceOf($role, $promise);
    }

    public function testEvalPromise(): void
    {
        $role = Entity::class;

        $orm = \Mockery::mock(ORMInterface::class);
        $schema = \Mockery::mock(SchemaInterface::class);
        $schema->shouldReceive('define')->andReturn($role);
        $orm->shouldReceive('getSchema')->andReturn($schema);

        $container = new Container();
        $container->bind(ORMInterface::class, $orm);

        $materializer = $container->get(EvalMaterializer::class);
        $container->bind(MaterializerInterface::class, $materializer);

        /** @var Factory $factory */
        $factory = $container->get(Factory::class);

        /** @var Entity $promise */
        $promise = $factory->promise($orm, $role, []);

        $this->assertInstanceOf($role, $promise);
    }
}