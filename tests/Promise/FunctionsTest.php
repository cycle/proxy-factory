<?php

/**
 * Spiral Framework. Cycle ProxyFactory
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */
declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests;

use PHPUnit\Framework\TestCase;

use function Cycle\ORM\Promise\injectValues;
use function Cycle\ORM\Promise\shortName;
use function Cycle\ORM\Promise\trimTrailingDigits;

class FunctionsTest extends TestCase
{
    /**
     * @dataProvider trailingProvider
     *
     * @param string $name
     * @param int    $sequence
     * @param string $expected
     */
    public function testTrimTrailingDigits(string $name, int $sequence, string $expected): void
    {
        $this->assertEquals($expected, trimTrailingDigits($name, $sequence));
    }

    public function trailingProvider(): array
    {
        return [
            ['name7', 7, 'name',],
            ['name', 0, 'name',],
            ['name0', 0, 'name',],
            ['name1', 1, 'name'],
            ['name-1', 1, 'name-'],
            ['name-1', -1, 'name'],
        ];
    }

    /**
     * @dataProvider injectValuesProvider
     *
     * @param array $expected
     * @param array $array
     * @param int   $index
     * @param array $injection
     */
    public function testInjectValues(array $expected, array $array, int $index, array $injection): void
    {
        $this->assertEquals($expected, injectValues($array, $index, $injection));
    }

    public function injectValuesProvider(): array
    {
        return [
            [
                ['aa', 'bb', 'a', 'b', 'c', 'd', 'e'],
                ['a', 'b', 'c', 'd', 'e'],
                0,
                ['aa', 'bb'],
            ],
            [
                ['a', 'b', 'c', 'aa', 'bb', 'd', 'e'],
                ['a', 'b', 'c', 'd', 'e'],
                -2,
                ['aa', 'bb'],
            ],
            [
                ['a', 'b', 'aa', 'bb', 'c', 'd', 'e'],
                ['a', 'b', 'c', 'd', 'e'],
                2,
                ['aa', 'bb'],
            ],
            [
                ['a', 'b', 'c', 'd', 'e', 'aa', 'bb'],
                ['a', 'b', 'c', 'd', 'e'],
                5,
                ['aa', 'bb'],
            ]
        ];
    }

    /**
     * @dataProvider shortNameProvider
     *
     * @param string $name
     * @param string $expected
     */
    public function testShortName(string $name, string $expected): void
    {
        $this->assertEquals($expected, shortName($name));
    }

    public function shortNameProvider(): array
    {
        return [
            ['a\b\cdef', 'cdef'],
            ['abcdef', 'abcdef'],
            ['abcdef\\', ''],
        ];
    }
}
