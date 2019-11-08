<?php

declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests\ProxyPrinter\Methods;

use Cycle\ORM\Promise\Declaration\Declarations;
use Cycle\ORM\Promise\Tests\ProxyPrinter\BaseProxyPrinterTest;

class MethodArgsTest extends BaseProxyPrinterTest
{
    /**
     * @throws \ReflectionException
     */
    public function testHasArgType(): void
    {
        $classname = Fixtures\ArgsFixture::class;
        $as = self::NS . __CLASS__ . __LINE__;
        $reflection = new \ReflectionClass($classname);

        $parent = Declarations::createParentFromReflection($reflection);
        $class = Declarations::createClassFromName($as, $parent);

        $output = $this->make($reflection, $class, $parent);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($class->getFullName()));

        eval($output);

        $this->assertStringContainsString('typedSetter(string $a, $b, int $c)', $output);
    }

    /**
     * @throws \ReflectionException
     */
    public function testArgDefaults(): void
    {
        $classname = Fixtures\ArgsFixture::class;
        $as = self::NS . __CLASS__ . __LINE__;
        $reflection = new \ReflectionClass($classname);

        $parent = Declarations::createParentFromReflection($reflection);
        $class = Declarations::createClassFromName($as, $parent);

        $output = $this->make($reflection, $class, $parent);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($class->getFullName()));

        eval($output);

        //Long syntax by default
        $this->assertStringContainsString('defaultsSetter(string $a, $b = array(), int $c = 3, bool $d)', $output);
    }

    public function testArgVariadic(): void
    {
        $this->assertTrue(true);
    }
}
