<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests\Declaration\ConflictResolver;

use Cycle\ORM\Promise\ConflictResolver\Name;
use PHPUnit\Framework\TestCase;

class NameTest extends TestCase
{
    /**
     * @dataProvider nameProvider
     *
     * @param string $name
     * @param int    $sequence
     * @param string $expected
     */
    public function testName(string $name, int $sequence, string $expected)
    {
        $this->assertEquals($expected, Name::createWithSequence($name, $sequence)->fullName());
    }

    public function nameProvider(): array
    {
        return [
            ['name', 7, 'name7'],
            ['name', 0, 'name'],
            ['name', -1, 'name'],
            ['name', 1, 'name1'],
        ];
    }
}