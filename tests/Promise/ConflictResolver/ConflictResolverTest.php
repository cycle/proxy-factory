<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests\Declaration\ConflictResolver;

use Cycle\ORM\Promise\ConflictResolver;
use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;

class ConflictResolverTest extends TestCase
{
    /**
     * @dataProvider cdProvider
     *
     * @param array $reserved
     * @param array $input
     * @param array $expected
     */
    public function testFind(array $reserved, array $input, array $expected)
    {
        $resolver = $this->conflictResolver();

        foreach ($input as $i => $name) {
            $this->assertEquals($expected[$i], $resolver->resolve($reserved, $name));
        }
    }

    private function conflictResolver(): ConflictResolver
    {
        $container = new Container();

        return $container->get(ConflictResolver::class);
    }

    public function cdProvider(): array
    {
        return [
            [
                [],
                ['v2', 'v', 'vv'],
                ['v2', 'v', 'vv']
            ],
            [
                ['v', 'v2'],
                ['v2', 'v', 'v1', 'vv', 't1', 't2'],
                ['v3', 'v3', 'v1', 'vv', 't1', 't2']
            ],
        ];
    }
}