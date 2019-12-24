<?php

/**
 * Spiral Framework. Cycle ProxyFactory
 *
 * @license MIT
 * @author  Valentin V (vvval)
 */

declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests\Declaration\ConflictResolver;

use Cycle\ORM\Promise\ConflictResolver;
use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Throwable;

class ConflictResolverTest extends TestCase
{
    /**
     * @dataProvider cdProvider
     *
     * @param array $reserved
     * @param array $input
     * @param array $expected
     * @throws Throwable
     */
    public function testFind(array $reserved, array $input, array $expected): void
    {
        $resolver = $this->conflictResolver();

        foreach ($input as $i => $name) {
            $this->assertEquals($expected[$i], $resolver->resolve($reserved, $name)->fullName());
        }
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

    /**
     * @return ConflictResolver
     * @throws Throwable
     */
    private function conflictResolver(): ConflictResolver
    {
        $container = new Container();

        return $container->get(ConflictResolver::class);
    }
}
