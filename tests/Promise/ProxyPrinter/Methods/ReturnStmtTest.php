<?php

/**
 * Spiral Framework. Cycle ProxyFactory
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */

declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests\ProxyPrinter\Methods;

use Cycle\ORM\Promise\Declaration\Declarations;
use Cycle\ORM\Promise\Exception\ProxyFactoryException;
use Cycle\ORM\Promise\Tests\ProxyPrinter\BaseProxyPrinterTest;
use ReflectionClass;
use ReflectionException;
use Throwable;

class ReturnStmtTest extends BaseProxyPrinterTest
{
    /**
     * @throws ReflectionException
     * @throws ProxyFactoryException
     * @throws Throwable
     */
    public function testSetter(): void
    {
        $classname = Fixtures\ChildFixture::class;
        $as = self::NS . __CLASS__ . __LINE__;
        $reflection = new ReflectionClass($classname);

        $parent = Declarations::createParentFromReflection($reflection);
        $class = Declarations::createClassFromName($as, $parent);

        $output = $this->make($reflection, $class, $parent);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($class->getFullName()));

        eval($output);
        $this->assertRegExp('/\s.*setter\(/', $output);
        $this->assertNotRegExp('/return\s.*setter\(/', $output);
    }

    /**
     * @throws ReflectionException
     * @throws ProxyFactoryException
     * @throws Throwable
     */
    public function testGetter(): void
    {
        $classname = Fixtures\ChildFixture::class;
        $as = self::NS . __CLASS__ . __LINE__;
        $reflection = new ReflectionClass($classname);

        $parent = Declarations::createParentFromReflection($reflection);
        $class = Declarations::createClassFromName($as, $parent);

        $output = $this->make($reflection, $class, $parent);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($class->getFullName()));

        eval($output);
        $this->assertRegExp('/return\s.*getter\(/', $output);
        $this->assertRegExp('/return\s.*hasReturn\(/', $output);
    }

    /**
     * @throws ReflectionException
     * @throws ProxyFactoryException
     * @throws Throwable
     */
    public function testConditionalReturn(): void
    {
        $classname = Fixtures\ChildFixture::class;
        $as = self::NS . __CLASS__ . __LINE__;
        $reflection = new ReflectionClass($classname);

        $parent = Declarations::createParentFromReflection($reflection);
        $class = Declarations::createClassFromName($as, $parent);

        $output = $this->make($reflection, $class, $parent);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($class->getFullName()));

        eval($output);
        $this->assertRegExp('/return\s.*conditionalReturn\(/', $output);
    }

    /**
     * @throws ReflectionException
     * @throws ProxyFactoryException
     * @throws Throwable
     */
    public function testVoidReturn(): void
    {
        $classname = Fixtures\ChildFixture::class;
        $as = self::NS . __CLASS__ . __LINE__;
        $reflection = new ReflectionClass($classname);

        $parent = Declarations::createParentFromReflection($reflection);
        $class = Declarations::createClassFromName($as, $parent);

        $output = $this->make($reflection, $class, $parent);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($class->getFullName()));

        eval($output);
        $this->assertRegExp('/\s.*voidReturn\(/', $output);
        $this->assertNotRegExp('/return\s.*voidReturn\(/', $output);
    }

    /**
     * @throws ProxyFactoryException
     * @throws ReflectionException
     * @throws Throwable
     */
    public function testRefReturn(): void
    {
        $classname = Fixtures\ChildFixture::class;
        $as = self::NS . __CLASS__ . __LINE__;
        $reflection = new ReflectionClass($classname);

        $parent = Declarations::createParentFromReflection($reflection);
        $class = Declarations::createClassFromName($as, $parent);

        $output = $this->make($reflection, $class, $parent);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($class->getFullName()));

        eval($output);
        $this->assertRegExp('/\s.*\&refReturn\(/', $output);
    }
}
