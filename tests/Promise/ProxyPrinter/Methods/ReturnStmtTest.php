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
use Cycle\ORM\Promise\Tests\ProxyPrinter\BaseProxyPrinterTest;

class ReturnStmtTest extends BaseProxyPrinterTest
{
    /**
     * @throws \ReflectionException
     */
    public function testSetter(): void
    {
        $classname = Fixtures\ChildFixture::class;
        $as = self::NS . __CLASS__ . __LINE__;
        $reflection = new \ReflectionClass($classname);

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
     * @throws \ReflectionException
     */
    public function testGetter(): void
    {
        $classname = Fixtures\ChildFixture::class;
        $as = self::NS . __CLASS__ . __LINE__;
        $reflection = new \ReflectionClass($classname);

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
     * @throws \ReflectionException
     */
    public function testConditionalReturn(): void
    {
        $classname = Fixtures\ChildFixture::class;
        $as = self::NS . __CLASS__ . __LINE__;
        $reflection = new \ReflectionClass($classname);

        $parent = Declarations::createParentFromReflection($reflection);
        $class = Declarations::createClassFromName($as, $parent);

        $output = $this->make($reflection, $class, $parent);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($class->getFullName()));

        eval($output);
        $this->assertRegExp('/return\s.*conditionalReturn\(/', $output);
    }

    /**
     * @throws \ReflectionException
     */
    public function testVoidReturn(): void
    {
        $classname = Fixtures\ChildFixture::class;
        $as = self::NS . __CLASS__ . __LINE__;
        $reflection = new \ReflectionClass($classname);

        $parent = Declarations::createParentFromReflection($reflection);
        $class = Declarations::createClassFromName($as, $parent);

        $output = $this->make($reflection, $class, $parent);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($class->getFullName()));

        eval($output);
        $this->assertRegExp('/\s.*voidReturn\(/', $output);
        $this->assertNotRegExp('/return\s.*voidReturn\(/', $output);
    }
}
