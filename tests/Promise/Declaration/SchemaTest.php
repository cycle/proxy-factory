<?php
declare(strict_types=1);

namespace Spiral\Cycle\Promise\Tests\Declaration;

use PHPUnit\Framework\TestCase;
use Spiral\Cycle\Promise\Declaration\Schema;

class SchemaTest extends TestCase
{
    /**
     * @dataProvider classNameProvider
     *
     * @param string $class
     * @param        $expected
     */
    public function testClassName(string $class, $expected)
    {
        $schema = new Schema('Any', $class);
        $this->assertSame($expected, $schema->class->class);
    }

    public function classNameProvider(): array
    {
        return [
            ['ExampleProxy', 'ExampleProxy'],
            ['\ExampleProxy', 'ExampleProxy'],
            ['Path\To\Proxy\ExampleProxy', 'ExampleProxy'],
        ];
    }

    /**
     * @dataProvider classNamespaceProvider
     *
     * @param string $extends
     * @param string $class
     * @param        $expected
     */
    public function testClassNamespace(string $extends, string $class, $expected)
    {
        $schema = new Schema($extends, $class);
        $this->assertSame($expected, $schema->class->namespace);
    }

    public function classNamespaceProvider(): array
    {
        return [
            ['Example', 'ExampleProxy', null],
            ['Example', '\ExampleProxy', null],
            ['Path\To\Example', '\ExampleProxy', null],
            ['Path\To\Example', 'ExampleProxy', 'Path\To'],
            ['Path\To\Example', 'Path\To\Proxy\ExampleProxy', 'Path\To\Proxy'],
        ];
    }

    /**
     * @dataProvider extendsNameProvider
     *
     * @param string $extends
     * @param        $expected
     */
    public function testExtendsName(string $extends, $expected)
    {
        $schema = new Schema($extends, 'Any');
        $this->assertSame($expected, $schema->extends->class);
    }

    public function extendsNameProvider(): array
    {
        return [
            ['Example', 'Example'],
            ['\Example', 'Example'],
            ['Path\To\Proxy\Example', 'Example'],
        ];
    }

    /**
     * @dataProvider extendsNamespaceProvider
     *
     * @param string $extends
     * @param        $expected
     */
    public function testExtendsNamespace(string $extends, $expected)
    {
        $schema = new Schema($extends, 'Any');
        $this->assertSame($expected, $schema->extends->namespace);
    }

    public function extendsNamespaceProvider(): array
    {
        return [
            ['Example', null],
            ['\Example', null],
            ['Path\To\Example', 'Path\To'],
        ];
    }
}