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
use Cycle\ORM\Promise\Declaration\Extractor;
use Cycle\ORM\Promise\Declaration\Structure;
use Cycle\ORM\Promise\Exception\ProxyFactoryException;
use Cycle\ORM\Promise\PromiseInterface;
use Cycle\ORM\Promise\Tests\ProxyPrinter\BaseProxyPrinterTest;
use PhpParser\Node\Stmt\ClassMethod;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Spiral\Core\Container;
use Throwable;
use UnexpectedValueException;

class MethodsTest extends BaseProxyPrinterTest
{
    /**
     * @throws ReflectionException
     * @throws ProxyFactoryException
     * @throws Throwable
     */
    public function testPromiseMethods(): void
    {
        $classname = Fixtures\EntityWithMethods::class;
        $as = self::NS . __CLASS__ . __LINE__;
        $reflection = new ReflectionClass($classname);

        $parent = Declarations::createParentFromReflection($reflection);
        $class = Declarations::createClassFromName($as, $parent);

        $output = $this->make($reflection, $class, $parent);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($class->getFullName()));

        eval($output);

        $methods = [];
        $reflection = new ReflectionClass($as);
        foreach ($reflection->getMethods() as $method) {
            $methods[$method->name] = $method->name;
        }

        $this->assertArrayHasKey('undefinedReturn', $methods);

        foreach ($this->interfaceMethods() as $method) {
            $this->assertArrayHasKey($method, $methods);
        }
    }

    /**
     * @throws ReflectionException
     * @throws ProxyFactoryException
     * @throws Throwable
     */
    public function testPromiseMethodsCache(): void
    {
        $classname = Fixtures\EntityWithMethods::class;
        $as = self::NS . __CLASS__ . __LINE__;
        $reflection = new ReflectionClass($classname);

        $parent = Declarations::createParentFromReflection($reflection);
        $class = Declarations::createClassFromName($as, $parent);

        $outputFirst = $this->make($reflection, $class, $parent);
        $fileName = $reflection->getFileName();
        rename($fileName, $fileName . '_old');
        $outputSecond = $this->make($reflection, $class, $parent);
        rename($fileName . '_old', $fileName);
        //get information from cache. Result same inspire that file was changed
        $this->assertEquals($outputFirst, $outputSecond);

        Extractor\Methods::resetNodesCache();
        rename($fileName, $fileName . '_old');
        Extractor\Methods::resetNodesCache();
        $outputSecond = $this->make($reflection, $class, $parent);
        rename($fileName . '_old', $fileName);
        //get information when cache was cleared. Result not same because of file changing
        $this->assertNotEquals($outputFirst, $outputSecond);
    }

    /**
     * @throws ReflectionException
     * @throws ProxyFactoryException
     * @throws Throwable
     * @throws Throwable
     */
    public function testInheritedMethods(): void
    {
        $classname = Fixtures\ChildEntityWithMethods::class;
        $as = self::NS . __CLASS__ . __LINE__;
        $reflection = new ReflectionClass($classname);

        $parent = Declarations::createParentFromReflection($reflection);
        $class = Declarations::createClassFromName($as, $parent);

        $output = $this->make($reflection, $class, $parent);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($class->getFullName()));

        eval($output);

        //There're only public and protected methods inside (not static and not final)
        $originMethods = [];
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED) as $method) {
            if ($method->isStatic() || $method->isFinal()) {
                continue;
            }

            $originMethods[$method->name] = $method->isPublic() ? 'public' : 'protected';
        }

        $methods = $this->promiseMethods($class->getFullName());
        $this->assertArrayHasKey('undefinedReturn', $methods);
        $this->assertArrayHasKey('undefinedReturn2', $methods);

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
                throw new UnexpectedValueException("\"{$method->name->toString()}\" method not found");
            }
        }
    }

    /**
     * @param ReflectionClass $class
     * @return Structure
     * @throws ReflectionException
     * @throws Throwable
     */
    private function getStructure(ReflectionClass $class): Structure
    {
        return $this->extractor()->extract($class);
    }

    /**
     * @return Extractor
     * @throws Throwable
     */
    private function extractor(): Extractor
    {
        $container = new Container();

        return $container->get(Extractor::class);
    }

    /**
     * @param string $classname
     * @return ClassMethod[]
     * @throws ReflectionException
     * @throws Throwable
     */
    private function promiseMethods(string $classname): array
    {
        $interfaceMethods = $this->interfaceMethods();
        $methods = [];
        $reflection = new ReflectionClass($classname);
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
        $reflection = new ReflectionClass(PromiseInterface::class);
        foreach ($reflection->getMethods() as $method) {
            $methods[$method->name] = $method->name;
        }

        return $methods;
    }
}
