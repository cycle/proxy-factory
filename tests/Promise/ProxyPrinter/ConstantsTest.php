<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests\ProxyPrinter;

use Cycle\ORM\Promise\Declaration\Declarations;

class ConstantsTest extends BaseProxyPrinterTest
{
    /**
     * @throws \ReflectionException
     */
    public function testWithoutConflicts(): void
    {
        $classname = Fixtures\EntityWithoutConstConflicts::class;
        $as = 'Cycle\ORM\Promise\Tests\Promises\EntityProxy' .__CLASS__ . __LINE__;
        $reflection = new \ReflectionClass($classname);

        $parent = Declarations::createParentFromReflection($reflection);
        $class = Declarations::createClassFromName($as, $parent);

        $output = $this->make($reflection, $class, $parent);
        $output = ltrim($output, '<?php');

        $this->assertStringNotContainsString('PUBLIC_CONST ', $output);
        $this->assertStringNotContainsString('PROTECTED_CONST ', $output);
        $this->assertStringNotContainsString('PRIVATE_CONST ', $output);
        $this->assertStringContainsString('UNSET_PROPERTIES ', $output);

        $this->assertFalse(class_exists($class->getFullName()));

        eval($output);

        $reflection = new \ReflectionClass($as);
        $this->assertArrayHasKey('PUBLIC_CONST', $reflection->getConstants());
        $this->assertArrayHasKey('PROTECTED_CONST', $reflection->getConstants());
        $this->assertArrayHasKey('UNSET_PROPERTIES', $reflection->getConstants());
    }

    /**
     * @throws \ReflectionException
     */
    public function testWithConflicts(): void
    {
        $classname = Fixtures\EntityWithConstConflicts::class;
        $as = 'Cycle\ORM\Promise\Tests\Promises\EntityProxy' . __CLASS__ .__LINE__;
        $reflection = new \ReflectionClass($classname);

        $parent = Declarations::createParentFromReflection($reflection);
        $class = Declarations::createClassFromName($as, $parent);

        $output = $this->make($reflection, $class, $parent);
        $output = ltrim($output, '<?php');

        $this->assertStringNotContainsString('PUBLIC_CONST ', $output);
        $this->assertStringNotContainsString('PROTECTED_CONST ', $output);
        $this->assertStringNotContainsString('PRIVATE_CONST ', $output);
        $this->assertStringNotContainsString('UNSET_PROPERTIES ', $output);
        $this->assertStringContainsString('UNSET_PROPERTIES_2 ', $output);

        $this->assertFalse(class_exists($class->getFullName()));

        eval($output);

        $reflection = new \ReflectionClass($as);
        $this->assertArrayHasKey('PUBLIC_CONST', $reflection->getConstants());
        $this->assertArrayHasKey('PROTECTED_CONST', $reflection->getConstants());
        $this->assertArrayHasKey('UNSET_PROPERTIES', $reflection->getConstants());
        $this->assertArrayHasKey('UNSET_PROPERTIES_2', $reflection->getConstants());
    }
}