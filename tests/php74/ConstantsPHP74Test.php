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
use Cycle\ORM\Promise\Exception\ProxyFactoryException;
use Cycle\ORM\Promise\Printer;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use Spiral\Core\Container;
use Throwable;

use function Cycle\ORM\Promise\phpVersionBetween;

class ConstantsPHP74Test extends TestCase
{
    protected const NS = 'Cycle\ORM\Promise\Tests74\Promises';

    /** @var Container */
    protected $container;

    public function setUp(): void
    {
        parent::setUp();

        $this->container = new Container();
    }

    /**
     * @throws ProxyFactoryException
     * @throws ReflectionException
     * @throws Throwable
     */
    public function testConstValues(): void
    {
        $classname = Fixtures\EntityWithProperties74::class;
        $as = self::NS . __CLASS__ . __LINE__;
        $reflection = new ReflectionClass($classname);

        $parent = Declarations::createParentFromReflection($reflection);
        $class = Declarations::createClassFromName($as, $parent);

        $output = $this->make($reflection, $class, $parent);
        $output = ltrim($output, '<?php');

        $this->assertStringContainsString(
            "PUBLIC_PROPERTIES = ['publicProperty', 'publicTypedProperty', 'publicPropertyWithDefaults'];",
            $output
        );

        /**
         * Since php7.4.1 the behaviour changed as it was before php7.4.0. All properties should be unset.
         * @see https://github.com/php/php-src/pull/4974
         */
        $this->assertStringContainsString(
            phpVersionBetween('7.4.0', '7.4.1')
                ? "UNSET_PROPERTIES = ['publicProperty', 'publicPropertyWithDefaults'];"
                : "UNSET_PROPERTIES = ['publicProperty', 'publicTypedProperty', 'publicPropertyWithDefaults'];",
            $output
        );
    }

    /**
     * @param ReflectionClass      $reflection
     * @param DeclarationInterface $class
     * @param DeclarationInterface $parent
     * @return string
     * @throws ProxyFactoryException
     * @throws ReflectionException
     * @throws Throwable
     */
    protected function make(
        ReflectionClass $reflection,
        DeclarationInterface $class,
        DeclarationInterface $parent
    ): string {
        return $this->proxyCreator()->make($reflection, $class, $parent);
    }

    /**
     * @return Printer
     * @throws Throwable
     */
    private function proxyCreator(): Printer
    {
        $this->container->bind(PrettyPrinterAbstract::class, Standard::class);

        return $this->container->get(Printer::class);
    }
}
