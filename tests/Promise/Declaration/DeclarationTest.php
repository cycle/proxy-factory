<?php

declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests\Declaration;

use Cycle\ORM\Promise\Declaration\Declarations;
use Cycle\ORM\Promise\Tests\Declaration\Fixtures\HasNamespaceExample;
use PHPUnit\Framework\TestCase;

class DeclarationTest extends TestCase
{
    /**
     * @dataProvider nameProvider
     *
     * @param \ReflectionClass $reflection
     * @param string           $name
     * @param string           $expected
     */
    public function testShortName(\ReflectionClass $reflection, string $name, string $expected): void
    {
        $parent = Declarations::createParentFromReflection($reflection);
        $class = Declarations::createClassFromName($name, $parent);

        $this->assertSame($expected, $class->getShortName());
    }

    public function nameProvider(): array
    {
        $r = new \ReflectionClass(\Example::class);

        return [
            [$r, 'ExampleProxy\\', 'ExampleProxy'],
            [$r, 'ExampleProxy', 'ExampleProxy'],
            [$r, '\ExampleProxy', 'ExampleProxy'],
            [$r, '\Namespaced\Name\Of\ExampleProxy', 'ExampleProxy'],
        ];
    }

    /**
     * @dataProvider namespaceProvider
     *
     * @param \ReflectionClass $reflection
     * @param string           $name
     * @param string|null      $expected
     */
    public function testNamespace(\ReflectionClass $reflection, string $name, ?string $expected): void
    {
        $parent = Declarations::createParentFromReflection($reflection);
        $class = Declarations::createClassFromName($name, $parent);

        $this->assertSame($expected, $class->getNamespaceName());
    }

    public function namespaceProvider(): array
    {
        $r1 = new \ReflectionClass(\Example::class);
        $r2 = new \ReflectionClass(HasNamespaceExample::class);

        return [
            [$r1, 'ExampleProxy\\', null],
            [$r1, 'ExampleProxy', null],
            [$r1, '\ExampleProxy', null],
            [$r2, '\ExampleProxy', null],
            [$r2, 'ExampleProxy', $r2->getNamespaceName()],
            [$r2, 'ExampleProxy\\', $r2->getNamespaceName()],
            [$r2, '\Namespaced\Name\Of\ExampleProxy', 'Namespaced\Name\Of'],
            [$r2, 'Namespaced\Name\Of\ExampleProxy', 'Namespaced\Name\Of'],
        ];
    }

    /**
     * @dataProvider fullNameProvider
     *
     * @param \ReflectionClass $reflection
     * @param string           $name
     * @param string|null      $expected
     */
    public function testFullName(\ReflectionClass $reflection, string $name, ?string $expected): void
    {
        $parent = Declarations::createParentFromReflection($reflection);
        $class = Declarations::createClassFromName($name, $parent);

        $this->assertSame($expected, $class->getFullName());
    }

    public function fullNameProvider(): array
    {
        $r1 = new \ReflectionClass(\Example::class);
        $r2 = new \ReflectionClass(HasNamespaceExample::class);

        return [
            [$r1, 'ExampleProxy', '\ExampleProxy'],
            [$r1, '\ExampleProxy', '\ExampleProxy'],
            [$r2, '\ExampleProxy', '\ExampleProxy'],
            [$r2, 'ExampleProxy', $r2->getNamespaceName() . '\\' . 'ExampleProxy'],
            [$r2, 'ExampleProxy\\', $r2->getNamespaceName() . '\\' . 'ExampleProxy'],
            [$r2, '\Namespaced\Name\Of\ExampleProxy', 'Namespaced\Name\Of\ExampleProxy'],
            [$r2, 'Namespaced\Name\Of\ExampleProxy', 'Namespaced\Name\Of\ExampleProxy'],
        ];
    }
}
