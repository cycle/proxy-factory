<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\Promise\Declaration\Declaration;
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
    public function testDeclaration()
    {
        $class = Fixtures\Entity::class;
        $as = "EntityProxy" . __LINE__;

        $output = $this->make($class, $as);
        $output = ltrim($output, "<?php");

        $schema = new Declaration($class, $as);
        $this->assertFalse(class_exists($schema->class->getNamespacesName()));

        eval($output);

        $this->assertStringNotContainsString('abstract', $output);
        $this->assertStringContainsString(sprintf(
            "class %s extends %s implements %s",
            $as,
            Utils::shortName($class),
            Utils::shortName(PromiseInterface::class)
        ), $output);

        $proxy = $this->makeProxyObject($class, $schema->class->getNamespacesName());

        $this->assertInstanceOf($schema->class->getNamespacesName(), $proxy);
        $this->assertInstanceOf($class, $proxy);
        $this->assertInstanceOf(PromiseInterface::class, $proxy);
    }

    /**
     * @throws \ReflectionException
     */
    public function testSameNamespace()
    {
        $class = Fixtures\Entity::class;
        $as = "EntityProxy" . __LINE__;

        $output = $this->make($class, $as);
        $output = ltrim($output, "<?php");

        $schema = new Declaration($class, $as);
        $this->assertFalse(class_exists($schema->class->getNamespacesName()));

        eval($output);

        $origReflection = new \ReflectionClass($class);
        $proxyReflection = new \ReflectionClass($schema->class->getNamespacesName());
        $this->assertSame($origReflection->getNamespaceName(), $proxyReflection->getNamespaceName());
    }

    /**
     * @throws \ReflectionException
     */
    public function testDifferentNamespace()
    {
        $class = Fixtures\Entity::class;
        $as = "\EntityProxy" . __LINE__;

        $output = $this->make($class, $as);
        $output = ltrim($output, "<?php");

        $schema = new Declaration($class, $as);
        $this->assertFalse(class_exists($schema->class->getNamespacesName()));

        eval($output);

        $proxyReflection = new \ReflectionClass($schema->class->getNamespacesName());
        $this->assertSame('', (string)$proxyReflection->getNamespaceName());
        $this->assertStringNotContainsString('namespace ', $output);
    }

    /**
     * @throws \ReflectionException
     */
    public function testUseStmtsInSameNamespace()
    {
        $class = Fixtures\Entity::class;
        $as = "EntityProxy" . __LINE__;

        $output = $this->make($class, $as);
        $output = ltrim($output, "<?php");

        $schema = new Declaration($class, $as);
        $this->assertFalse(class_exists($schema->class->getNamespacesName()));

        eval($output);

        $this->assertSame($this->fetchUseStatements($output), $this->fetchExternalDependencies($schema->class->getNamespacesName(), [
            PromiseResolver::class,
            PromiseInterface::class
        ]));
    }

    /**
     * @throws \ReflectionException
     */
    public function testUseStmtsInDifferentNamespace()
    {
        $class = Fixtures\Entity::class;
        $as = "\EntityProxy" . __LINE__;

        $output = $this->make($class, $as);
        $output = ltrim($output, "<?php");

        $schema = new Declaration($class, $as);
        $this->assertFalse(class_exists($schema->class->getNamespacesName()));

        eval($output);

        $this->assertSame($this->fetchUseStatements($output), $this->fetchExternalDependencies($schema->class->getNamespacesName(), [
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

    public function testTraits()
    {
        $this->assertStringNotContainsString(' use ', $this->make(Fixtures\EntityWithoutTrait::class, "EntityProxy" . __LINE__));
        $this->assertStringNotContainsString(' use ', $this->make(Fixtures\EntityWithTrait::class, "EntityProxy" . __LINE__));
    }

    public function testConstants()
    {
        $this->assertStringNotContainsString(' const ', $this->make(Fixtures\EntityWithoutConstants::class, "EntityProxy" . __LINE__));
        $this->assertStringNotContainsString(' const ', $this->make(Fixtures\EntityWithConstants::class, "EntityProxy" . __LINE__));
    }

    public function testProperties()
    {
        $class = Fixtures\Entity::class;
        $as = "EntityProxy" . __LINE__;

        $output = $this->make($class, $as);
        $output = ltrim($output, "<?php");

        $schema = new Declaration($class, $as);
        $this->assertFalse(class_exists($schema->class->getNamespacesName()));

        eval($output);

        $reflection = new \ReflectionClass($schema->class->getNamespacesName());

        /** @var \ReflectionProperty[] $properties */
        $properties = [];
        foreach ($reflection->getProperties() as $property) {
            if ($property->getDeclaringClass()->getName() !== $schema->class->getNamespacesName()) {
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
    public function testHasConstructor()
    {
        $class = Fixtures\EntityWithoutConstructor::class;
        $as = "EntityProxy" . __LINE__;

        $output = $this->make($class, $as);
        $output = ltrim($output, "<?php");

        $schema = new Declaration($class, $as);
        $this->assertFalse(class_exists($schema->class->getNamespacesName()));

        eval($output);

        $reflection = new \ReflectionClass($schema->class->getNamespacesName());
        $constructor = $reflection->getConstructor();
        $this->assertNotNull($constructor);

        $class = Fixtures\EntityWithConstructor::class;
        $as = "EntityProxy" . __LINE__;

        $output = $this->make($class, $as);
        $output = ltrim($output, "<?php");

        $schema = new Declaration($class, $as);
        $this->assertFalse(class_exists($schema->class->getNamespacesName()));

        eval($output);

        $reflection = new \ReflectionClass($schema->class->getNamespacesName());
        $constructor = $reflection->getConstructor();
        $this->assertNotNull($constructor);
    }

    public function testNotContainParentConstructor()
    {
        $class = Fixtures\EntityWithoutConstructor::class;
        $as = "EntityProxy" . __LINE__;

        $output = $this->make($class, $as);
        $output = ltrim($output, "<?php");

        $schema = new Declaration($class, $as);
        $this->assertFalse(class_exists($schema->class->getNamespacesName()));

        eval($output);

        $this->assertStringNotContainsString('parent::__construct();', $output);
    }

    public function testContainParentConstructor()
    {
        $class = Fixtures\EntityWithConstructor::class;
        $as = "EntityProxy" . __LINE__;

        $output = $this->make($class, $as);
        $output = ltrim($output, "<?php");

        $schema = new Declaration($class, $as);
        $this->assertFalse(class_exists($schema->class->getNamespacesName()));

        eval($output);

        $this->assertStringContainsString('parent::__construct();', $output);
    }

    public function testPromiseMethods()
    {
        $class = Fixtures\Entity::class;
        $as = "EntityProxy" . __LINE__;

        $output = $this->make($class, $as);
        $output = ltrim($output, "<?php");

        $schema = new Declaration($class, $as);
        $this->assertFalse(class_exists($schema->class->getNamespacesName()));

        eval($output);

        $i = new \ReflectionClass(PromiseInterface::class);
        foreach ($i->getMethods() as $method) {
            $this->assertStringContainsString("public function {$method->getName()}()", $output);
        }
    }

    public function testProxiedMethods()
    {
        $class = Fixtures\Entity::class;
        $as = "EntityProxy" . __LINE__;

        $output = $this->make($class, $as);
        $output = ltrim($output, "<?php");

        $schema = new Declaration($class, $as);
        $this->assertFalse(class_exists($schema->class->getNamespacesName()));

        eval($output);

        $d = $this->getDeclaration($class);
        foreach ($d->methods as $method) {
            if ($method->isPublic()) {
                $this->assertStringContainsString("public function {$method->name->name}()", $output);
            } elseif ($method->isProtected()) {
                $this->assertStringContainsString("protected function {$method->name->name}()", $output);
            } else {
                throw new \UnexpectedValueException("\"{$method->name->toString()}\" method not found");
            }
        }
    }

    private function getDeclaration(string $class): Structure
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

    private function make(string $class, string $as): string
    {
        return $this->proxyCreator()->make($class, $as);
    }

    private function proxyCreator(): ProxyPrinter
    {
        $container = new Container();
        $container->bind(PrettyPrinterAbstract::class, Standard::class);

        return $container->get(ProxyPrinter::class);
    }
}