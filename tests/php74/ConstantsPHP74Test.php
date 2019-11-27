<?php

/**
 * Spiral Framework. Cycle ProxyFactory
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */

declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests74;

use Cycle\ORM\Promise\Declaration\DeclarationInterface;
use Cycle\ORM\Promise\Declaration\Declarations;
use Cycle\ORM\Promise\Printer;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;
use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;

class ConstantsPHP74Test extends TestCase
{
    protected const NS = 'Cycle\ORM\Promise\Tests74\Promises';

    /** @var \Spiral\Core\Container */
    protected $container;

    public function setUp(): void
    {
        parent::setUp();

        $this->container = new Container();
    }

    /**
     * @throws \Cycle\ORM\Promise\Exception\ProxyFactoryException
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function testConstValues(): void
    {
        $classname = Fixtures\EntityWithProperties74::class;
        $as = self::NS . __CLASS__ . __LINE__;
        $reflection = new \ReflectionClass($classname);

        $parent = Declarations::createParentFromReflection($reflection);
        $class = Declarations::createClassFromName($as, $parent);

        $output = $this->make($reflection, $class, $parent);
        $output = ltrim($output, '<?php');

        $this->assertStringContainsString(
            "PUBLIC_PROPERTIES = ['publicProperty', 'publicTypedProperty', 'publicPropertyWithDefaults'];",
            $output
        );

        $this->assertStringContainsString(
            "UNSET_PROPERTIES = ['publicProperty', 'publicPropertyWithDefaults'];",
            $output
        );
    }

    /**
     * @param \ReflectionClass     $reflection
     * @param DeclarationInterface $class
     * @param DeclarationInterface $parent
     * @return string
     * @throws \Cycle\ORM\Promise\Exception\ProxyFactoryException
     * @throws \ReflectionException
     * @throws \Throwable
     */
    protected function make(
        \ReflectionClass $reflection,
        DeclarationInterface $class,
        DeclarationInterface $parent
    ): string {
        return $this->proxyCreator()->make($reflection, $class, $parent);
    }

    /**
     * @return \Cycle\ORM\Promise\Printer
     * @throws \Throwable
     */
    private function proxyCreator(): Printer
    {
        $this->container->bind(PrettyPrinterAbstract::class, Standard::class);

        return $this->container->get(Printer::class);
    }
}
