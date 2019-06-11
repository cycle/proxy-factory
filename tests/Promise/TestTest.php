<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests;


use Cycle\Annotated\Entities;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\Promise\Declaration\Declarations;
use Cycle\ORM\Promise\Factory;
use Cycle\ORM\Promise\MaterializerInterface;
use Cycle\ORM\Promise\Printer;
use Cycle\ORM\Promise\Tests\Fixtures\SchematicEntity;
use Cycle\Schema;
use Spiral\Core\Container;
use Spiral\Database\Driver\SQLite\SQLiteDriver;

class TestTest extends BaseTest
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

        $this->container = new Container();
        $this->container->bind(ORMInterface::class, $this->orm());
    }

    /**
     * @throws \Cycle\ORM\Promise\ProxyFactoryException
     * @throws \ReflectionException
     */
    public function testStmts(): void
    {
//        $classname = \Example::class;
//        $classname = Fixtures\Entity::class;
        $classname = SchematicEntity::class;
        $reflection = new \ReflectionClass($classname);

        $as = '\\New\\WClass';

        $parent = Declarations::createParentFromReflection($reflection);
        $class = Declarations::createClassFromName($as, $parent);

        $o=$this->printer()->make($reflection, $class, $parent);
//        dump($o);
//        eval($o);

        $this->assertTrue(true);
    }

    private function printer(): Printer
    {
        return $this->container->get(Printer::class);
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