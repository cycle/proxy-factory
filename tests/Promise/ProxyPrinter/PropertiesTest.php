<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests\ProxyPrinter;

use Cycle\ORM\Promise\Declaration\Declarations;
use Cycle\ORM\Promise\Tests\ProxyPrinter\Fixtures;

class PropertiesTest extends BaseProxyPrinterTest
{
    /**
     * @throws \ReflectionException
     */
    public function testWithoutConflicts(): void
    {
        $classname = Fixtures\EntityWithoutPropConflicts::class;
        $as = 'Cycle\ORM\Promise\Tests\Promises\EntityProxy' . __CLASS__ . __LINE__;
        $reflection = new \ReflectionClass($classname);

        $parent = Declarations::createParentFromReflection($reflection);
        $class = Declarations::createClassFromName($as, $parent);

        $output = $this->make($reflection, $class, $parent);
        $output = ltrim($output, '<?php');

        $this->assertStringNotContainsString('$public;', $output);
        $this->assertStringNotContainsString('$publicStatic;', $output);
        $this->assertStringNotContainsString('$protected;', $output);
        $this->assertStringNotContainsString('$private;', $output);
        $this->assertStringContainsString('$__resolver;', $output);

        $this->assertFalse(class_exists($class->getFullName()));

        eval($output);

        $reflection = new \ReflectionClass($class->getFullName());

        /** @var \ReflectionProperty[] $properties */
        $properties = [];
        foreach ($reflection->getProperties() as $property) {
            $properties[$property->name] = $property;
        }

        $this->assertArrayHasKey('public', $properties);
        $this->assertArrayHasKey('publicStatic', $properties);
        $this->assertArrayHasKey('protected', $properties);
        $this->assertArrayHasKey('__resolver', $properties);

        $property = $properties['__resolver'];
        $this->assertTrue($property->isPrivate());
        $this->assertFalse($property->isStatic());
    }

    /**
     * @throws \ReflectionException
     */
    public function testWithConflicts(): void
    {
        $classname = Fixtures\EntityWithPropConflicts::class;
        $as = 'Cycle\ORM\Promise\Tests\Promises\EntityProxy' . __CLASS__ . __LINE__;
        $reflection = new \ReflectionClass($classname);

        $parent = Declarations::createParentFromReflection($reflection);
        $class = Declarations::createClassFromName($as, $parent);

        $output = $this->make($reflection, $class, $parent);
        $output = ltrim($output, '<?php');

        $this->assertStringNotContainsString('$public;', $output);
        $this->assertStringNotContainsString('$publicStatic;', $output);
        $this->assertStringNotContainsString('$protected;', $output);
        $this->assertStringNotContainsString('$private;', $output);
        $this->assertStringNotContainsString('$__resolver;', $output);
        $this->assertStringContainsString('$__resolver2;', $output);

        $this->assertFalse(class_exists($class->getFullName()));

        eval($output);

        $reflection = new \ReflectionClass($class->getFullName());

        /** @var \ReflectionProperty[] $properties */
        $properties = [];
        foreach ($reflection->getProperties() as $property) {
            $properties[$property->name] = $property;
        }

        $this->assertArrayHasKey('public', $properties);
        $this->assertArrayHasKey('publicStatic', $properties);
        $this->assertArrayHasKey('protected', $properties);
        $this->assertArrayHasKey('__resolver', $properties);
        $this->assertArrayHasKey('__resolver2', $properties);

        $property = $properties['__resolver2'];
        $this->assertTrue($property->isPrivate());
        $this->assertFalse($property->isStatic());
    }
}