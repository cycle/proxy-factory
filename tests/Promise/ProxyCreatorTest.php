<?php
declare(strict_types=1);

namespace Spiral\Cycle\Promise\Tests;

use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;
use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Cycle\ORM;
use Spiral\Cycle\ORMInterface;
use Spiral\Cycle\Promise\Declaration\Declaration;
use Spiral\Cycle\Promise\Declaration\Extractor;
use Spiral\Cycle\Promise\PromiseInterface;
use Spiral\Cycle\Promise\PromiseResolver;
use Spiral\Cycle\Promise\ProxyCreator;
use Spiral\Cycle\Promise\ResolverTrait;
use Spiral\Cycle\Promise\Tests\Fixtures;
use Spiral\Cycle\Promise\Utils;
use Spiral\Cycle\Select\SourceFactoryInterface;

class ProxyCreatorTest extends TestCase
{
    public function testDeclaration()
    {
        $className = Fixtures\Entity::class;
        $as = "EntityProxy" . __LINE__;

        $output = $this->make($className, $as);
        $output = ltrim($output, "<?php");

        $this->assertStringNotContainsString('abstract', $output);
        $this->assertStringContainsString(sprintf(
            "\nclass %s extends %s implements %s\n",
            $as,
            Utils::shortName($className),
            Utils::shortName(PromiseInterface::class)
        ), $output);

        $proxyFullName = $this->changeName($className, $as);
        $this->assertFalse(class_exists($proxyFullName));

        eval($output);

        $origReflection = new \ReflectionClass($className);
        $proxyReflection = new \ReflectionClass($proxyFullName);
        $this->assertSame($origReflection->getNamespaceName(), $proxyReflection->getNamespaceName());

        $container = new Container();
        $container->bind(ORMInterface::class, ORM::class);
        $container->bind(SourceFactoryInterface::class, ORM::class);
        $proxy = $container->make($proxyFullName, ['target' => Fixtures\Entity::class, 'scope' => []]);

        $this->assertInstanceOf($proxyFullName, $proxy);
        $this->assertInstanceOf(Fixtures\Entity::class, $proxy);
        $this->assertInstanceOf(PromiseInterface::class, $proxy);
    }

    /**
     * @depends testDeclaration
     * @throws \ReflectionException
     */
    public function testNamespace()
    {
        $className = Fixtures\Entity::class;
        $as = "EntityProxy" . __LINE__;

        $output = $this->make($className, $as);
        $output = ltrim($output, "<?php");

        $proxyFullName = $this->changeName($className, $as);
        $this->assertFalse(class_exists($proxyFullName));

        eval($output);

        $origReflection = new \ReflectionClass($className);
        $proxyReflection = new \ReflectionClass($proxyFullName);
        $this->assertSame($origReflection->getNamespaceName(), $proxyReflection->getNamespaceName());
    }

    /**
     * @depends testHasConstructor
     * @throws \ReflectionException
     */
    public function testUse()
    {
        $className = Fixtures\Entity::class;
        $as = "EntityProxy" . __LINE__;

        $output = $this->make($className, $as);
        $output = ltrim($output, "<?php");

        $proxyFullName = $this->changeName($className, $as);
        $this->assertFalse(class_exists($proxyFullName));

        eval($output);

        $this->assertSame($this->fetchUseStatements($output), $this->fetchExternalDependencies($proxyFullName));
    }

    public function testTrait()
    {
        $this->assertStringContainsString(sprintf(
            "use %s, %s;",
            Utils::shortName(Fixtures\EntityTrait::class),
            Utils::shortName(ResolverTrait::class)
        ), $this->make(Fixtures\EntityWithTrait::class, "EntityProxy" . __LINE__));

        $this->assertStringContainsString(sprintf(
            "use %s;",
            Utils::shortName(ResolverTrait::class)
        ), $this->make(Fixtures\EntityWithoutTrait::class, "EntityProxy" . __LINE__));
    }

    /**
     * @depends testDeclaration
     */
    public function testProperties()
    {
    }

    /**
     * @throws \ReflectionException
     */
    public function testHasConstructor()
    {
        $className = Fixtures\EntityWithoutConstructor::class;
        $as = "EntityProxy" . __LINE__;

        $output = $this->make($className, $as);
        $output = ltrim($output, "<?php");

        $proxyFullName = $this->changeName($className, $as);
        $this->assertFalse(class_exists($proxyFullName));

        eval($output);

        $reflection = new \ReflectionClass($proxyFullName);
        $constructor = $reflection->getConstructor();
        $this->assertNotNull($constructor);
    }

    /**
     * @depends testDeclaration
     */
    public function testNotContainParentConstructor()
    {
        $className = Fixtures\EntityWithoutConstructor::class;
        $as = "EntityProxy" . __LINE__;

        $output = $this->make($className, $as);
        $output = ltrim($output, "<?php");

        $proxyFullName = $this->changeName($className, $as);
        $this->assertFalse(class_exists($proxyFullName));

        eval($output);

        $this->assertStringNotContainsString('parent::__construct();', $output);
    }

    /**
     * @depends testDeclaration
     */
    public function testContainParentConstructor()
    {
        $className = Fixtures\EntityWithConstructor::class;
        $as = "EntityProxy" . __LINE__;

        $output = $this->make($className, $as);
        $output = ltrim($output, "<?php");

        $proxyFullName = $this->changeName($className, $as);
        $this->assertFalse(class_exists($proxyFullName));

        eval($output);

        $this->assertStringContainsString('parent::__construct();', $output);
    }

    /**
     * @depends testDeclaration
     */
    public function testMethods()
    {
    }

    private function fetchUseStatements(string $code): array
    {
        $uses = [];
        foreach (explode("\n", $code) as $line) {
            if (mb_stripos($line, 'use') !== 0) {
                continue;
            }

            $uses[] = trim(mb_substr($line, 4), ' ;');
        }

        sort($uses);

        return $uses;
    }

    /**
     * @param string $class
     *
     * @return array
     * @throws \ReflectionException
     */
    private function fetchExternalDependencies(string $class): array
    {
        $reflection = new \ReflectionClass($class);
        $types = [
            ResolverTrait::class,
            PromiseResolver::class,
            PromiseInterface::class
        ];
        foreach ($reflection->getConstructor()->getParameters() as $parameter) {
            if (!$parameter->hasType() || $parameter->getType()->isBuiltin()) {
                continue;
            }

            $types[] = $parameter->getType()->getName();
        }

        sort($types);

        return $types;
    }

    private function changeName(string $class, string $as): string
    {
        return mb_substr($class, 0, mb_strrpos($class, '\\')) . "\\$as";
    }

    private function make(string $class, string $as): string
    {
        return $this->proxyCreator()->make($class, $as, $this->getDeclaration($class));
    }

    private function getDeclaration(string $class): Declaration
    {
        $class = new \ReflectionClass($class);

        return $this->extractor()->extract($class->getFileName());
    }

    private function extractor(): Extractor
    {
        $container = new Container();

        return $container->get(Extractor::class);
    }

    private function proxyCreator(): ProxyCreator
    {
        $container = new Container();
        $container->bind(PrettyPrinterAbstract::class, Standard::class);

        return $container->get(ProxyCreator::class);
    }
}