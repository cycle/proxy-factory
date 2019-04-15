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
     * @param \ReflectionClass $parent
     * @param string           $class
     * @param string           $expected
     */
    public function testShortName(\ReflectionClass $parent, string $class, string $expected): void
    {
        $declaration = Declarations::createFromReflection($parent, $class);
        $this->assertSame($expected, $declaration->class->getShortName());
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
     * @param \ReflectionClass $parent
     * @param string           $class
     * @param string|null      $expected
     */
    public function testNamespace(\ReflectionClass $parent, string $class, ?string $expected): void
    {
        $declaration = Declarations::createFromReflection($parent, $class);
        $this->assertSame($expected, $declaration->class->getNamespaceName());
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
     * @param \ReflectionClass $parent
     * @param string           $class
     * @param string|null      $expected
     */
    public function testFullName(\ReflectionClass $parent, string $class, ?string $expected): void
    {
        $declaration = Declarations::createFromReflection($parent, $class);
        $this->assertSame($expected, $declaration->class->getFullName());
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