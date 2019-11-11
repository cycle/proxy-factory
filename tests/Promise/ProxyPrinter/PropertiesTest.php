<?php

/**
 * Spiral Framework. Cycle ProxyFactory
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */

declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests\ProxyPrinter;

use Cycle\ORM\Promise\Declaration\Declarations;
use Cycle\ORM\Promise\Tests\ProxyPrinter\Fixtures;

class PropertiesTest extends BaseProxyPrinterTest
{
    /**
     * @throws \Cycle\ORM\Promise\Exception\ProxyFactoryException
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function testWithoutConflicts(): void
    {
        $classname = Fixtures\EntityWithoutPropConflicts::class;
        $as = self::NS . __CLASS__ . __LINE__;
        $reflection = new \ReflectionClass($classname);

        $parent = Declarations::createParentFromReflection($reflection);
        $class = Declarations::createClassFromName($as, $parent);

        $output = $this->make($reflection, $class, $parent);
        $output = ltrim($output, '<?php');

        $this->assertStringNotContainsString('$public;', $output);
        $this->assertStringNotContainsString('$publicStatic;', $output);
        $this->assertStringNotContainsString('$protected;', $output);
        $this->assertStringNotContainsString('$private;', $output);
        $this->assertStringContainsString('$resolver;', $output);

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
        $this->assertArrayHasKey('resolver', $properties);

        $property = $properties['resolver'];
        $this->assertTrue($property->isPrivate());
        $this->assertFalse($property->isStatic());
    }

    /**
     * @throws \Cycle\ORM\Promise\Exception\ProxyFactoryException
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function testWithConflicts(): void
    {
        $classname = Fixtures\EntityWithPropConflicts::class;
        $as = self::NS . __CLASS__ . __LINE__;
        $reflection = new \ReflectionClass($classname);

        $parent = Declarations::createParentFromReflection($reflection);
        $class = Declarations::createClassFromName($as, $parent);

        $output = $this->make($reflection, $class, $parent);
        $output = ltrim($output, '<?php');

        $this->assertStringNotContainsString('$public;', $output);
        $this->assertStringNotContainsString('$publicStatic;', $output);
        $this->assertStringNotContainsString('$protected;', $output);
        $this->assertStringNotContainsString('$private;', $output);
        $this->assertStringNotContainsString('$resolver;', $output);
        $this->assertStringContainsString('$resolver2;', $output);

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
        $this->assertArrayHasKey('resolver', $properties);
        $this->assertArrayHasKey('resolver2', $properties);

        $property = $properties['resolver2'];
        $this->assertTrue($property->isPrivate());
        $this->assertFalse($property->isStatic());
    }
}
