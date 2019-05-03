<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests;

use Cycle\Annotated\Entities;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\Promise\Declaration\DeclarationInterface;
use Cycle\ORM\Promise\Declaration\Declarations;
use Cycle\ORM\Promise\Declaration\Extractor;
use Cycle\ORM\Promise\Declaration\Structure;
use Cycle\ORM\Promise\PromiseInterface;
use Cycle\ORM\Promise\PromiseResolver;
use Cycle\ORM\Promise\ProxyPrinter;
use Cycle\ORM\Promise\Tests\Fixtures;
use Cycle\ORM\Promise\Utils;
use Cycle\Schema;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;
use Spiral\Core\Container;
use Spiral\Database\Driver\SQLite\SQLiteDriver;

class ProxyPrinterTest extends BaseTest
{
    public const DRIVER = 'sqlite';

    /** @var \Spiral\Core\Container */
    private $container;

    public function setUp()
    {
        self::$config = [
            'debug'     => false,
            'strict'    => true,
            'benchmark' => false,
            'sqlite'    => [
                'driver' => SQLiteDriver::class,
                'check'  => static function () {
                    return !in_array('sqlite', \PDO::getAvailableDrivers(), true);
                },
                'conn'   => 'sqlite::memory:',
                'user'   => 'sqlite',
                'pass'   => ''
            ],
        ];

        parent::setUp();

        $this->container = new Container();
        $this->container->bind(ORMInterface::class, $this->orm());
    }

    public function testDeclaration(): void
    {
        $classname = Fixtures\Entity::class;
        $as = 'EntityProxy' . __LINE__;

        $r = new \ReflectionClass($classname);
        $parent = Declarations::createParentFromReflection($r);
        $class = Declarations::createClassFromName($as, $parent);
        $output = $this->make($r, $class, $parent);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($class->getFullName()));

        eval($output);

        $this->assertStringNotContainsString('abstract', $output);
        $this->assertStringContainsString(sprintf(
            'class %s extends %s implements %s',
            $as,
            Utils::shortName($classname),
            Utils::shortName(PromiseInterface::class)
        ), $output);

        $proxy = $this->makeProxyObject($classname, $class->getFullName());

        $this->assertInstanceOf($class->getFullName(), $proxy);
        $this->assertInstanceOf($classname, $proxy);
        $this->assertInstanceOf(PromiseInterface::class, $proxy);
    }

    /**
     * @throws \ReflectionException
     */
    public function testSameNamespace(): void
    {
        $classname = Fixtures\Entity::class;
        $as = 'EntityProxy' . __LINE__;

        $r = new \ReflectionClass($classname);
        $parent = Declarations::createParentFromReflection($r);
        $class = Declarations::createClassFromName($as, $parent);
        $output = $this->make($r, $class, $parent);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($class->getFullName()));

        eval($output);

        $origReflection = new \ReflectionClass($classname);
        $proxyReflection = new \ReflectionClass($class->getFullName());
        $this->assertSame($origReflection->getNamespaceName(), $proxyReflection->getNamespaceName());
    }

    /**
     * @throws \ReflectionException
     */
    public function testDifferentNamespace(): void
    {
        $classname = Fixtures\Entity::class;
        $as = "\EntityProxy" . __LINE__;

        $r = new \ReflectionClass($classname);
        $parent = Declarations::createParentFromReflection($r);
        $class = Declarations::createClassFromName($as, $parent);
        $output = $this->make($r, $class, $parent);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($class->getFullName()));

        eval($output);

        $proxyReflection = new \ReflectionClass($class->getFullName());
        $this->assertSame('', (string)$proxyReflection->getNamespaceName());
        $this->assertStringNotContainsString('namespace ', $output);
    }

    /**
     * @throws \ReflectionException
     */
    public function testUseStmtsInSameNamespace(): void
    {
        $classname = Fixtures\Entity::class;
        $as = 'EntityProxy' . __LINE__;

        $r = new \ReflectionClass($classname);
        $parent = Declarations::createParentFromReflection($r);
        $class = Declarations::createClassFromName($as, $parent);
        $output = $this->make($r, $class, $parent);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($class->getFullName()));

        eval($output);

        $this->assertSame($this->fetchUseStatements($output), $this->fetchExternalDependencies($class->getFullName(), [
            PromiseResolver::class,
            PromiseInterface::class
        ]));
    }

    /**
     * @throws \ReflectionException
     */
    public function testUseStmtsInDifferentNamespace(): void
    {
        $classname = Fixtures\Entity::class;
        $as = "\EntityProxy" . __LINE__;

        $r = new \ReflectionClass($classname);
        $parent = Declarations::createParentFromReflection($r);
        $class = Declarations::createClassFromName($as, $parent);
        $output = $this->make($r, $class, $parent);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($class->getFullName()));

        eval($output);

        $this->assertSame($this->fetchUseStatements($output), $this->fetchExternalDependencies($class->getFullName(), [
            PromiseResolver::class,
            PromiseInterface::class,
            $classname
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

    /**
     * @dataProvider traitsDataProvider
     *
     * @param string $classname
     * @param string $as
     *
     * @throws \ReflectionException
     */
    public function testTraits(string $classname, string $as): void
    {
        $r = new \ReflectionClass($classname);
        $parent = Declarations::createParentFromReflection($r);
        $class = Declarations::createClassFromName($as, $parent);
        $this->assertStringNotContainsString(' use ', $this->make($r, $class, $parent));
    }

    public function traitsDataProvider(): array
    {
        return [
            [Fixtures\EntityWithoutTrait::class, 'EntityProxy' . __LINE__],
            [Fixtures\EntityWithTrait::class, 'EntityProxy' . __LINE__],
        ];
    }

    /**
     * @dataProvider constantsDataProvider
     *
     * @param string $classname
     * @param string $as
     *
     * @throws \ReflectionException
     */
    public function testConstants(string $classname, string $as): void
    {
        $r = new \ReflectionClass($classname);
        $parent = Declarations::createParentFromReflection($r);
        $class = Declarations::createClassFromName($as, $parent);
        $this->assertStringNotContainsString(' const ', $this->make($r, $class, $parent));
    }

    public function constantsDataProvider(): array
    {
        return [
            [Fixtures\EntityWithoutTrait::class, 'EntityProxy' . __LINE__],
            [Fixtures\EntityWithTrait::class, 'EntityProxy' . __LINE__],
        ];
    }

    public function testProperties(): void
    {
        $classname = Fixtures\Entity::class;
        $as = 'EntityProxy' . __LINE__;

        $r = new \ReflectionClass($classname);
        $parent = Declarations::createParentFromReflection($r);
        $class = Declarations::createClassFromName($as, $parent);

        $output = $this->make($r, $class, $parent);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($class->getFullName()));

        eval($output);

        $reflection = new \ReflectionClass($class->getFullName());

        /** @var \ReflectionProperty[] $properties */
        $properties = [];
        foreach ($reflection->getProperties() as $property) {
            if ($property->getDeclaringClass()->name !== $class->getFullName()) {
                continue;
            }

            $properties[] = $property;
        }

        $this->assertCount(1, $properties);
        $property = $properties[0];
        $this->assertTrue($property->isPrivate());
        $this->assertFalse($property->isStatic());
        $this->assertStringContainsString('@var PromiseResolver|' . Utils::shortName($classname), $property->getDocComment());
    }

    /**
     * @throws \ReflectionException
     */
    /**
     * @dataProvider constructorDataProvider
     *
     * @param string $class
     * @param string $as
     *
     * @throws \ReflectionException
     */
    public function testHasConstructor(string $class, string $as): void
    {
        $r = new \ReflectionClass($class);
        $parent = Declarations::createParentFromReflection($r);
        $class = Declarations::createClassFromName($as, $parent);
        $output = $this->make($r, $class, $parent);
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
            [Fixtures\EntityWithoutConstructor::class, 'EntityProxy' . __LINE__],
            [Fixtures\EntityWithConstructor::class, 'EntityProxy' . __LINE__],
        ];
    }

    public function testNotContainParentConstructor(): void
    {
        $class = Fixtures\EntityWithoutConstructor::class;
        $as = 'EntityProxy' . __LINE__;

        $r = new \ReflectionClass($class);
        $parent = Declarations::createParentFromReflection($r);
        $class = Declarations::createClassFromName($as, $parent);
        $output = $this->make($r, $class, $parent);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($class->getFullName()));

        eval($output);

        $this->assertStringNotContainsString('parent::__construct();', $output);
    }

    public function testContainParentConstructor(): void
    {
        $class = Fixtures\EntityWithConstructor::class;
        $as = 'EntityProxy' . __LINE__;

        $r = new \ReflectionClass($class);
        $parent = Declarations::createParentFromReflection($r);
        $class = Declarations::createClassFromName($as, $parent);
        $output = $this->make($r, $class, $parent);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($class->getFullName()));

        eval($output);

        $this->assertStringContainsString('parent::__construct();', $output);
    }

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

    public function testInheritedProperties(): void
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

        $sourceProperties = [];
        foreach ($reflection->getProperties() as $property) {
            $sourceProperties[] = $property->name;
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
        return $this->container->make($proxyFullName, ['role' => $className, 'scope' => []]);
    }

    private function make(\ReflectionClass $reflection, DeclarationInterface $class, DeclarationInterface $parent): string
    {
        return $this->proxyCreator()->make($reflection, $class, $parent);
    }

    private function proxyCreator(): ProxyPrinter
    {
        $container = new Container();
        $container->bind(PrettyPrinterAbstract::class, Standard::class);

        return $container->get(ProxyPrinter::class);
    }

    private function orm(): ORMInterface
    {
        $schema = (new Schema\Compiler())->compile(new Schema\Registry($this->dbal), [
            new Entities($this->locator),
            new Schema\Generator\ResetTables(),
            new Schema\Generator\GenerateRelations(),
            new Schema\Generator\ValidateEntities(),
            new Schema\Generator\RenderTables(),
            new Schema\Generator\RenderRelations(),
            new Schema\Generator\SyncTables(),
            new Schema\Generator\GenerateTypecast(),
        ]);

        return $this->withSchema(new \Cycle\ORM\Schema($schema));
    }
}