<?php

/**
 * Spiral Framework. Cycle ProxyFactory
 *
 * @license MIT
 * @author  Valentin V (vvval)
 */
declare(strict_types=1);

namespace Cycle\ORM\Promise;

use PhpParser\Node;

class StatementsInjector
{
    /**
     * @param Node\Stmt[] $stmts $stmts
     * @param string      $target
     * @param Node\Stmt[] $injection
     * @return Node\Stmt[]
     */
    public function inject(array $stmts, string $target, array $injection): array
    {
        return injectValues($stmts, $this->injectionID($stmts, $target), $injection);
    }

    /**
     * @param Node\Stmt[] $stmts
     * @param string      $target
     * @return int
     */
    private function injectionID(array $stmts, string $target): int
    {
        foreach ($stmts as $index => $child) {
            if ($child instanceof $target) {
                return $index;
            }
        }

        return 0;
    }
}
