<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\Promise\Declaration\Declarations;
use Cycle\ORM\Promise\Declaration\Extractor;
use Cycle\ORM\Promise\Declaration\Structure;
use Cycle\ORM\Promise\PromiseInterface;
use Cycle\ORM\Promise\PromiseResolver;
use Cycle\ORM\Promise\ProxyPrinter;
use Cycle\ORM\Promise\Tests\Fixtures;
use Cycle\ORM\Promise\Utils;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;
use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;

class ProxyPrinterTest extends TestCase
{
    public function testDeclaration(): void
    {
        $class = Fixtures\Entity::class;
        $as = 'EntityProxy' . __LINE__;

        $r = new \ReflectionClass($class);
        $declaration = Declarations::createFromReflection($r, $as);
        $output = $this->make($r, $declaration);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($declaration->class->getFullName()));

        eval($output);

        $this->assertStringNotContainsString('abstract', $output);
        $this->assertStringContainsString(sprintf(
            'class %s extends %s implements %s',
            $as,
            Utils::shortName($class),
            Utils::shortName(PromiseInterface::class)
        ), $output);

        $proxy = $this->makeProxyObject($class, $declaration->class->getFullName());

        $this->assertInstanceOf($declaration->class->getFullName(), $proxy);
        $this->assertInstanceOf($class, $proxy);
        $this->assertInstanceOf(PromiseInterface::class, $proxy);
    }

    /**
     * @throws \ReflectionException
     */
    public function testSameNamespace(): void
    {
        $class = Fixtures\Entity::class;
        $as = 'EntityProxy' . __LINE__;

        $r = new \ReflectionClass($class);
        $declaration = Declarations::createFromReflection($r, $as);
        $output = $this->make($r, $declaration);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($declaration->class->getFullName()));

        eval($output);

        $origReflection = new \ReflectionClass($class);
        $proxyReflection = new \ReflectionClass($declaration->class->getFullName());
        $this->assertSame($origReflection->getNamespaceName(), $proxyReflection->getNamespaceName());
    }

    /**
     * @throws \ReflectionException
     */
    public function testDifferentNamespace(): void
    {
        $class = Fixtures\Entity::class;
        $as = "\EntityProxy" . __LINE__;

        $r = new \ReflectionClass($class);
        $declaration = Declarations::createFromReflection($r, $as);
        $output = $this->make($r, $declaration);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($declaration->class->getFullName()));

        eval($output);

        $proxyReflection = new \ReflectionClass($declaration->class->getFullName());
        $this->assertSame('', (string)$proxyReflection->getNamespaceName());
        $this->assertStringNotContainsString('namespace ', $output);
    }

    /**
     * @throws \ReflectionException
     */
    public function testUseStmtsInSameNamespace(): void
    {
        $class = Fixtures\Entity::class;
        $as = 'EntityProxy' . __LINE__;

        $r = new \ReflectionClass($class);
        $declaration = Declarations::createFromReflection($r, $as);
        $output = $this->make($r, $declaration);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($declaration->class->getFullName()));

        eval($output);

        $this->assertSame($this->fetchUseStatements($output), $this->fetchExternalDependencies($declaration->class->getFullName(), [
            PromiseResolver::class,
            PromiseInterface::class
        ]));
    }

    /**
     * @throws \ReflectionException
     */
    public function testUseStmtsInDifferentNamespace(): void
    {
        $class = Fixtures\Entity::class;
        $as = "\EntityProxy" . __LINE__;

        $r = new \ReflectionClass($class);
        $declaration = Declarations::createFromReflection($r, $as);
        $output = $this->make($r, $declaration);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($declaration->class->getFullName()));

        eval($output);

        $this->assertSame($this->fetchUseStatements($output), $this->fetchExternalDependencies($declaration->class->getFullName(), [
            PromiseResolver::class,
            PromiseInterface::class,
            $class
        ]));
    }

    private function fetchUseStatements(string $code): array
    {
        $uses = [];
        foreach (explode("\n", $code) as $line) {
            if (mb_stripos($line, 'use') !== 0) {
                continue;
            }

            $uses[] = trim(mb_substr($line, 4), " ;\r\n");
        }

        sort($uses);

        return $uses;
    }

    /**
     * @param string $class
     * @param array  $types
     *
     * @return array
     * @throws \ReflectionException
     */
    private function fetchExternalDependencies(string $class, array $types = []): array
    {
        $reflection = new \ReflectionClass($class);

        foreach ($reflection->getConstructor()->getParameters() as $parameter) {
            if (!$parameter->hasType() || $parameter->getType()->isBuiltin()) {
                continue;
            }

            $types[] = $parameter->getType()->getName();
        }

        sort($types);

        return $types;
    }

    public function testTraits(): void
    {
        $r = new \ReflectionClass(Fixtures\EntityWithoutTrait::class);
        $this->assertStringNotContainsString(' use ', $this->make($r, Declarations::createFromReflection($r, 'EntityProxy' . __LINE__)));

        $r = new \ReflectionClass(Fixtures\EntityWithTrait::class);
        $this->assertStringNotContainsString(' use ', $this->make($r, Declarations::createFromReflection($r, 'EntityProxy' . __LINE__)));
    }

    public function testConstants(): void
    {
        $r = new \ReflectionClass(Fixtures\EntityWithoutConstants::class);
        $this->assertStringNotContainsString(' const ', $this->make($r, Declarations::createFromReflection($r, 'EntityProxy' . __LINE__)));

        $r = new \ReflectionClass(Fixtures\EntityWithConstants::class);
        $this->assertStringNotContainsString(' const ', $this->make($r, Declarations::createFromReflection($r, 'EntityProxy' . __LINE__)));
    }

    public function testProperties(): void
    {
        $class = Fixtures\Entity::class;
        $as = 'EntityProxy' . __LINE__;

        $r = new \ReflectionClass($class);
        $declaration = Declarations::createFromReflection($r, $as);
        $output = $this->make($r, $declaration);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($declaration->class->getFullName()));

        eval($output);

        $reflection = new \ReflectionClass($declaration->class->getFullName());

        /** @var \ReflectionProperty[] $properties */
        $properties = [];
        foreach ($reflection->getProperties() as $property) {
            if ($property->getDeclaringClass()->getName() !== $declaration->class->getFullName()) {
                continue;
            }

            $properties[] = $property;
        }

        $this->assertCount(1, $properties);
        $property = $properties[0];
        $this->assertTrue($property->isPrivate());
        $this->assertFalse($property->isStatic());
        $this->assertStringContainsString('@var PromiseResolver|' . Utils::shortName($class), $property->getDocComment());
    }

    /**
     * @throws \ReflectionException
     */
    public function testHasConstructor(): void
    {
        $class = Fixtures\EntityWithoutConstructor::class;
        $as = 'EntityProxy' . __LINE__;

        $r = new \ReflectionClass($class);
        $declaration = Declarations::createFromReflection($r, $as);
        $output = $this->make($r, $declaration);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($declaration->class->getFullName()));

        eval($output);

        $reflection = new \ReflectionClass($declaration->class->getFullName());
        $constructor = $reflection->getConstructor();
        $this->assertNotNull($constructor);

        $class = Fixtures\EntityWithConstructor::class;
        $as = 'EntityProxy' . __LINE__;

        $r = new \ReflectionClass($class);
        $declaration = Declarations::createFromReflection($r, $as);
        $output = $this->make($r, $declaration);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($declaration->class->getFullName()));

        eval($output);

        $reflection = new \ReflectionClass($declaration->class->getFullName());
        $constructor = $reflection->getConstructor();
        $this->assertNotNull($constructor);
    }

    public function testNotContainParentConstructor(): void
    {
        $class = Fixtures\EntityWithoutConstructor::class;
        $as = 'EntityProxy' . __LINE__;

        $r = new \ReflectionClass($class);
        $declaration = Declarations::createFromReflection($r, $as);
        $output = $this->make($r, $declaration);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($declaration->class->getFullName()));

        eval($output);

        $this->assertStringNotContainsString('parent::__construct();', $output);
    }

    public function testContainParentConstructor(): void
    {
        $class = Fixtures\EntityWithConstructor::class;
        $as = 'EntityProxy' . __LINE__;

        $r = new \ReflectionClass($class);
        $declaration = Declarations::createFromReflection($r, $as);
        $output = $this->make($r, $declaration);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($declaration->class->getFullName()));

        eval($output);

        $this->assertStringContainsString('parent::__construct();', $output);
    }

    public function testPromiseMethods(): void
    {
        $class = Fixtures\Entity::class;
        $as = 'EntityProxy' . __LINE__;

        $r = new \ReflectionClass($class);
        $declaration = Declarations::createFromReflection($r, $as);
        $output = $this->make($r, $declaration);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($declaration->class->getFullName()));

        eval($output);

        $i = new \ReflectionClass(PromiseInterface::class);
        foreach ($i->getMethods() as $method) {
            $this->assertStringContainsString("public function {$method->getName()}()", $output);
        }
    }

    public function testInheritedProperties(): void
    {
        $class = Fixtures\ChildEntity::class;
        $as = 'EntityProxy' . __LINE__;

        $reflection = new \ReflectionClass($class);

        $r = new \ReflectionClass($class);
        $declaration = Declarations::createFromReflection($r, $as);
        $output = $this->make($r, $declaration);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($declaration->class->getFullName()));

        eval($output);

        $sourceProperties = [];
        foreach ($reflection->getProperties() as $property) {
            $sourceProperties[] = $property->getName();
        }

        $properties = [];
        foreach ($this->getDeclaration($r)->properties as $property) {
            $properties[] = $property;
        }

        foreach ($sourceProperties as $property) {
            $this->assertArrayNotHasKey($property, $properties, "Proxied class contains not expected `{$property}` property");
            $this->assertStringNotContainsString(" $property;", $output);
        }

        foreach ($properties as $property) {
            $this->assertArrayNotHasKey($property, $sourceProperties, "Origin class contains not expected `{$property}` property");
        }
    }

    public function testInheritedMethods(): void
    {
        $class = Fixtures\ChildEntity::class;
        $as = 'EntityProxy' . __LINE__;

        $reflection = new \ReflectionClass($class);

        $r = new \ReflectionClass($class);
        $declaration = Declarations::createFromReflection($r, $as);
        $output = $this->make($r, $declaration);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($declaration->class->getFullName()));

        eval($output);

        $sourceMethods = [];

        //There're only public and protected methods inside
        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_PROTECTED) as $method) {
            $sourceMethods[$method->getName()] = $method->isPublic() ? 'public' : 'protected';
        }

        /** @var \PhpParser\Node\Stmt\ClassMethod[] $methods */
        $methods = [];
        foreach ($this->getDeclaration($r)->methods as $method) {
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

    private function getDeclaration(\ReflectionClass $class): Structure
    {
        return $this->extractor()->extract($class);
    }

    private function extractor(): Extractor
    {
        $container = new Container();

        return $container->get(Extractor::class);
    }

    /**
     * @param string $className
     * @param string $proxyFullName
     *
     * @return object
     */
    private function makeProxyObject(string $className, string $proxyFullName)
    {
        $orm = \Mockery::mock(ORMInterface::class);

        $container = new Container();
        $container->bind(ORMInterface::class, $orm);

        return $container->make($proxyFullName, ['role' => $className, 'scope' => []]);
    }

    private function make(\ReflectionClass $reflection, Declarations $declaration): string
    {
        return $this->proxyCreator()->make($reflection, $declaration);
    }

    private function proxyCreator(): ProxyPrinter
    {
        $container = new Container();
        $container->bind(PrettyPrinterAbstract::class, Standard::class);

        return $container->get(ProxyPrinter::class);
    }
}