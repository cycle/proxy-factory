<?php
declare(strict_types=1);


namespace Cycle\ORM\Promise\Tests\ProxyPrinter;


use Cycle\Annotated\Entities;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\Promise\Declaration\DeclarationInterface;
use Cycle\ORM\Promise\ProxyPrinter;
use Cycle\ORM\Promise\Tests\BaseTest;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;
use Spiral\Core\Container;
use Spiral\Database\Driver\SQLite\SQLiteDriver;
use Cycle\Schema;

abstract class BaseProxyPrinterTest extends BaseTest
{
    protected const NS = 'Cycle\ORM\Promise\Tests\Promises';
    public const DRIVER = 'sqlite';

    /** @var \Spiral\Core\Container */
    protected $container;

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

    protected function make(\ReflectionClass $reflection, DeclarationInterface $class, DeclarationInterface $parent): string
    {
        return $this->proxyCreator()->make($reflection, $class, $parent);
    }

    private function proxyCreator(): ProxyPrinter
    {
        $this->container->bind(PrettyPrinterAbstract::class, Standard::class);

        return $this->container->get(ProxyPrinter::class);
    }
}