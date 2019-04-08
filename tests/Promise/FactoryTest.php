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
//    public function testPromise(): void
//    {
//        $role = Entity::class;
//
//        $orm = \Mockery::mock(ORMInterface::class);
//        $schema = \Mockery::mock(SchemaInterface::class);
//        $schema->shouldReceive('define')->andReturn($role);
//        $orm->shouldReceive('getSchema')->andReturn($schema);
//
////        $materializer->shouldReceive('materialize');
//
//        $container = new Container();
//        $container->bind(ORMInterface::class, $orm);
//
//        $materializer = $container->make(FileMaterializer::class, ['directory' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'promises']);
//        $container->bind(MaterializerInterface::class, $materializer);
//
//        /** @var Factory $factory */
//        $factory = $container->get(Factory::class);
//        $promise = $factory->promise($orm, $role, []);
////        dump($promise);
//
//        $this->assertTrue(true);
//    }
}