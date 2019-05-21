<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests\ProxyPrinter;

use Cycle\ORM\Promise\Declaration\Declarations;

class NamespaceTest extends BaseProxyPrinterTest
{
    /**
     * @throws \ReflectionException
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