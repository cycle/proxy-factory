<?php

declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests\ProxyPrinter;

use Cycle\ORM\Promise\Declaration\Declarations;
use Cycle\ORM\Promise\Declaration\Extractor;
use Cycle\ORM\Promise\Declaration\Structure;
use Cycle\ORM\Promise\PromiseInterface;
use Spiral\Core\Container;

class MethodsTest extends BaseProxyPrinterTest
{
    /**
     * @throws \ReflectionException
     */
    public function testPromiseMethods(): void
    {
        $classname = Fixtures\EntityWithMethods::class;
        $as = self::NS . __CLASS__ . __LINE__;
        $reflection = new \ReflectionClass($classname);

        $parent = Declarations::createParentFromReflection($reflection);
        $class = Declarations::createClassFromName($as, $parent);

        $output = $this->make($reflection, $class, $parent);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($class->getFullName()));

        eval($output);

        $methods = [];
        $reflection = new \ReflectionClass($as);
        foreach ($reflection->getMethods() as $method) {
            $methods[$method->name] = $method->name;
        }

        $this->assertArrayHasKey('undefinedReturn', $methods);
        $this->assertRegExp('/return\s.*undefinedReturn\(/', $output);

        foreach ($this->interfaceMethods() as $method) {
            $this->assertArrayHasKey($method, $methods);
        }
    }

    /**
     * @throws \ReflectionException
     */
    public function testInheritedMethods(): void
    {
        $classname = Fixtures\ChildEntityWithMethods::class;
        $as = self::NS . __CLASS__ . __LINE__;
        $reflection = new \ReflectionClass($classname);

        $parent = Declarations::createParentFromReflection($reflection);
        $class = Declarations::createClassFromName($as, $parent);

        $output = $this->make($reflection, $class, $parent);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($class->getFullName()));

        eval($output);

        //There're only public and protected methods inside (not static and not final)
        $originMethods = [];
        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_PROTECTED) as $method) {
            if ($method->isStatic() || $method->isFinal()) {
                continue;
            }

            $originMethods[$method->name] = $method->isPublic() ? 'public' : 'protected';
        }

        $methods = $this->promiseMethods($class->getFullName());
        $this->assertArrayHasKey('undefinedReturn', $methods);
        $this->assertRegExp('/return\s.*undefinedReturn\(/', $output);

        $this->assertArrayHasKey('undefinedReturn2', $methods);
        $this->assertRegExp('/return\s.*undefinedReturn2\(/', $output);

        foreach ($originMethods as $name => $accessor) {
            $this->assertArrayHasKey($name, $methods, "Proxy class does not contain expected `{$name}` method");

            if ($accessor === 'public') {
                $this->assertTrue($methods[$name]->isPublic(), "Proxied method `{$name}` expected to be public");
            } else {
                $this->assertTrue($methods[$name]->isProtected(), "Proxied method `{$name}` expected to be protected");
            }
        }

        foreach ($methods as $name => $method) {
            //todo dirty test
            if (mb_strpos('__init', $name) === 0) {
                continue;
            }

            $this->assertArrayHasKey($name, $originMethods, "Origin class does not contain expected `{$name}` method");

            if ($method->isPublic()) {
                $this->assertEquals('public', $originMethods[$name], "Proxied method `{$name}` expected to be public");
            } elseif ($method->isProtected()) {
                $this->assertEquals(
                    'protected',
                    $originMethods[$name],
                    "Proxied method `{$name}` expected to be public"
                );
            } else {
                throw new \UnexpectedValueException("\"{$method->name->toString()}\" method not found");
            }
        }
    }

    private function getStructure(\ReflectionClass $class): Structure
    {
        return $this->extractor()->extract($class);
    }

    private function extractor(): Extractor
    {
        $container = new Container();

        return $container->get(Extractor::class);
    }

    /**
     * @param string $classname
     *
     * @return \PhpParser\Node\Stmt\ClassMethod[]
     * @throws \ReflectionException
     */
    private function promiseMethods(string $classname): array
    {
        $interfaceMethods = $this->interfaceMethods();
        $methods = [];
        $reflection = new \ReflectionClass($classname);
        foreach ($this->getStructure($reflection)->methods as $method) {
            if (isset($interfaceMethods[$method->name->name]) || $method->isMagic()) {
                continue;
            }

            $methods[$method->name->name] = $method;
        }

        return $methods;
    }

    private function interfaceMethods(): array
    {
        $methods = [];
        $reflection = new \ReflectionClass(PromiseInterface::class);
        foreach ($reflection->getMethods() as $method) {
            $methods[$method->name] = $method->name;
        }

        return $methods;
    }
}
