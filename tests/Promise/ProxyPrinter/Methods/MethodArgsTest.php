<?php

declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests\ProxyPrinter\Methods;

use Cycle\ORM\Promise\Declaration\Declarations;
use Cycle\ORM\Promise\Exception\ProxyFactoryException;
use Cycle\ORM\Promise\Tests\ProxyPrinter\BaseProxyPrinterTest;
use ReflectionClass;
use ReflectionException;
use Throwable;

class MethodArgsTest extends BaseProxyPrinterTest
{
    /**
     * @throws ReflectionException
     * @throws ProxyFactoryException
     * @throws Throwable
     */
    public function testHasArgType(): void
    {
        $output = $this->makeOutput(Fixtures\ArgsFixture::class, self::NS . __CLASS__ . __LINE__);

        $this->assertStringContainsString('typedSetter(string $a, $b, int $c)', $output);
    }

    /**
     * @throws ReflectionException
     * @throws ProxyFactoryException
     * @throws Throwable
     */
    public function testArgDefaults(): void
    {
        $output = $this->makeOutput(Fixtures\ArgsFixture::class, self::NS . __CLASS__ . __LINE__);

        //Long syntax by default
        $this->assertStringContainsString('defaultsSetter(string $a, $b = array(), int $c = 3, ?bool $d = null)', $output);
    }

    /**
     * @throws ReflectionException
     * @throws ProxyFactoryException
     * @throws Throwable
     */
    public function testVariadicArg(): void
    {
        $output = $this->makeOutput(Fixtures\ArgsFixture::class, self::NS . __CLASS__ . __LINE__);

        $this->assertStringContainsString('public function variadicSetter($a, string ...$b)', $output);
    }

    /**
     * @throws ReflectionException
     * @throws ProxyFactoryException
     * @throws Throwable
     */
    public function testReferencedArg(): void
    {
        $output = $this->makeOutput(Fixtures\ArgsFixture::class, self::NS . __CLASS__ . __LINE__);

        $this->assertStringContainsString('public function referencedSetter(string $a, &$b, int $c)', $output);
    }

    /**
     * @param string $classname
     * @param string $as
     * @return string
     * @throws ReflectionException
     * @throws ProxyFactoryException
     * @throws Throwable
     */
    private function makeOutput(string $classname, string $as): string
    {
        $reflection = new ReflectionClass($classname);

        $parent = Declarations::createParentFromReflection($reflection);
        $class = Declarations::createClassFromName($as, $parent);

        $output = $this->make($reflection, $class, $parent);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($class->getFullName()));

        eval($output);

        return $output;
    }
}
