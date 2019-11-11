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

class NamespaceTest extends BaseProxyPrinterTest
{
    /**
     * @throws \ReflectionException
     * @throws \Cycle\ORM\Promise\Exception\ProxyFactoryException
     * @throws \Throwable
     */
    public function testSameNamespace(): void
    {
        $classname = Fixtures\EntityInNamespace::class;
        $as = 'EntityProxy' . str_replace('\\', '', __CLASS__) . __LINE__;
        $reflection = new \ReflectionClass($classname);

        $parent = Declarations::createParentFromReflection($reflection);
        $class = Declarations::createClassFromName($as, $parent);

        $output = $this->make($reflection, $class, $parent);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($class->getFullName()));

        eval($output);

        $proxyReflection = new \ReflectionClass($class->getFullName());
        $this->assertSame($reflection->getNamespaceName(), $proxyReflection->getNamespaceName());
    }

    /**
     * @throws \ReflectionException
     * @throws \Cycle\ORM\Promise\Exception\ProxyFactoryException
     * @throws \Throwable
     */
    public function testDistinctNamespace(): void
    {
        $classname = Fixtures\EntityInNamespace::class;
        $as = '\\EntityProxy' . str_replace('\\', '', __CLASS__) . __LINE__;
        $reflection = new \ReflectionClass($classname);

        $parent = Declarations::createParentFromReflection($reflection);
        $class = Declarations::createClassFromName($as, $parent);

        $output = $this->make($reflection, $class, $parent);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($class->getFullName()));

        eval($output);

        $proxyReflection = new \ReflectionClass($class->getFullName());
        $this->assertSame('', (string)$proxyReflection->getNamespaceName());
        $this->assertStringNotContainsString('namespace ', $output);
    }
}
