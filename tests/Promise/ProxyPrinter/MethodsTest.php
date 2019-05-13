<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests\ProxyPrinter;

use Cycle\ORM\Promise\Declaration\Extractor;
use Cycle\ORM\Promise\Declaration\Structure;
use Cycle\ORM\Promise\Tests\Fixtures;
use Cycle\ORM\Promise\Declaration\Declarations;
use Cycle\ORM\Promise\PromiseInterface;
use Spiral\Core\Container;

class MethodsTest extends BaseProxyPrinterTest
{
    public function testPromiseMethods(): void
    {
        $class = Fixtures\Entity::class;
        $as = 'EntityProxy' . __LINE__;

        $r = new \ReflectionClass($class);
        $parent = Declarations::createParentFromReflection($r);
        $class = Declarations::createClassFromName($as, $parent);
        $output = $this->make($r, $class, $parent);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($class->getFullName()));

        eval($output);

        $i = new \ReflectionClass(PromiseInterface::class);
        foreach ($i->getMethods() as $method) {
            $this->assertStringContainsString("public function {$method->name}()", $output);
        }
    }

    public function testInheritedMethods(): void
    {
        $class = Fixtures\ChildEntity::class;
        $as = 'EntityProxy' . __LINE__;

        $reflection = new \ReflectionClass($class);

        $r = new \ReflectionClass($class);
        $parent = Declarations::createParentFromReflection($r);
        $class = Declarations::createClassFromName($as, $parent);
        $output = $this->make($r, $class, $parent);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($class->getFullName()));

        eval($output);

        $sourceMethods = [];

        //There're only public and protected methods inside
        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_PROTECTED) as $method) {
            $sourceMethods[$method->name] = $method->isPublic() ? 'public' : 'protected';
        }

        /** @var \PhpParser\Node\Stmt\ClassMethod[] $methods */
        $methods = [];
        foreach ($this->getStructure($r)->methods as $method) {
            $methods[$method->name->name] = $method;
        }

        foreach ($sourceMethods as $name => $accessor) {
            $this->assertArrayHasKey($name, $methods, "Proxy class does not contain expected `{$name}` method");

            if ($accessor === 'public') {
                $this->assertTrue($methods[$name]->isPublic(), "Proxied method `{$name}` expected to be public");
                $this->assertStringContainsString("public function {$name}()", $output);
            } else {
                $this->assertTrue($methods[$name]->isProtected(), "Proxied method `{$name}` expected to be protected");
                $this->assertStringContainsString("protected function {$name}()", $output);
            }
        }

        foreach ($methods as $name => $method) {
            $this->assertArrayHasKey($name, $sourceMethods, "Origin class does not contain expected `{$name}` method");

            if ($method->isPublic()) {
                $this->assertEquals('public', $sourceMethods[$name], "Proxied method `{$name}` expected to be public");
            } elseif ($method->isProtected()) {
                $this->assertEquals('protected', $sourceMethods[$name], "Proxied method `{$name}` expected to be public");
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
}