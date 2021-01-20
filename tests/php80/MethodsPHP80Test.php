<?php

declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests80;

use Cycle\ORM\Promise\Declaration\Declarations;
use Cycle\ORM\Promise\Exception\ProxyFactoryException;
use Cycle\ORM\Promise\Tests\ProxyPrinter\BaseProxyPrinterTest;
use Cycle\ORM\Promise\Tests80\Fixtures;
use ReflectionClass;
use ReflectionException;
use Throwable;

class MethodsPHP80Test extends BaseProxyPrinterTest
{
    /**
     * @throws ProxyFactoryException
     * @throws ReflectionException
     * @throws Throwable
     */
    public function testUnionTypes(): void
    {
        $output = $this->makeOutput(Fixtures\EntityWithUnionTypes::class, self::NS . __CLASS__ . __LINE__);

        $this->assertStringContainsString(
            'public function method(\Exception|\Error $arg): \Exception|\Error',
            $output
        );
    }

    /**
     * @throws ReflectionException
     * @throws ProxyFactoryException
     * @throws Throwable
     */
    public function testNullableMixedArg(): void
    {
        $output = $this->makeOutput(Fixtures\EntityWithMixedArg::class, self::NS . __CLASS__ . __LINE__);

        $this->assertStringContainsString(
            'public function method(mixed $arg): mixed',
            $output
        );
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
        $output = str_replace(') : ', '): ', $output);

        $this->assertFalse(class_exists($class->getFullName()));

        eval($output);

        return $output;
    }
}
