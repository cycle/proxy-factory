<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests\ProxyPrinter;

use Cycle\ORM\Promise\Declaration\Declarations;

class ConstructorTest extends BaseProxyPrinterTest
{
    /**
     * @dataProvider constructorDataProvider
     *
     * @param string $classname
     * @param string $as
     *
     * @throws \ReflectionException
     */
    public function testHasConstructor(string $classname, string $as): void
    {
        $reflection = new \ReflectionClass($classname);
        $parent = Declarations::createParentFromReflection($reflection);
        $class = Declarations::createClassFromName($as, $parent);
        $output = $this->make($reflection, $class, $parent);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($class->getFullName()));

        eval($output);

        $reflection = new \ReflectionClass($class->getFullName());
        $constructor = $reflection->getConstructor();
        $this->assertNotNull($constructor);
    }

    public function constructorDataProvider(): array
    {
        return [
            [Fixtures\EntityWithoutConstructor::class, 'EntityProxy' . __CLASS__ . __LINE__],
            [Fixtures\EntityWithConstructor::class, 'EntityProxy' . __CLASS__ . __LINE__],
        ];
    }

    /**
     * @throws \ReflectionException
     */
    public function testNotContainParentConstructor(): void
    {
        $class = Fixtures\EntityWithoutConstructor::class;
        $as = 'EntityProxy' . __CLASS__ . __LINE__;

        $r = new \ReflectionClass($class);
        $parent = Declarations::createParentFromReflection($r);
        $class = Declarations::createClassFromName($as, $parent);
        $output = $this->make($r, $class, $parent);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($class->getFullName()));

        eval($output);

        $this->assertStringNotContainsString('parent::__construct();', $output);
    }

    /**
     * @throws \ReflectionException
     */
    public function testContainParentConstructor(): void
    {
        $class = Fixtures\EntityWithConstructor::class;
        $as = 'EntityProxy' . __CLASS__ . __LINE__;

        $reflection = new \ReflectionClass($class);
        $parent = Declarations::createParentFromReflection($reflection);
        $class = Declarations::createClassFromName($as, $parent);
        $output = $this->make($reflection, $class, $parent);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($class->getFullName()));

        eval($output);

        $this->assertStringContainsString('parent::__construct();', $output);
    }

}