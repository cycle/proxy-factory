<?php

/**
 * Spiral Framework. Cycle ProxyFactory
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */

declare(strict_types=1);

namespace Cycle\ORM\Promise;

use PhpParser\Node;

/**
 * @param Node\Stmt[] $stmts $stmts
 * @param string      $target
 * @param Node\Stmt[] $injection
 * @return Node\Stmt[]
 */
function inject(array $stmts, string $target, array $injection): array
{
    return injectValues($stmts, injectionID($stmts, $target), $injection);
}

/**
 * Inject values to array at given index.
 *
 * @param array $stmts
 * @param int   $index
 * @param array $values
 * @return array
 */
function injectValues(array $stmts, int $index, array $values): array
{
    $before = array_slice($stmts, 0, $index);
    $after = array_slice($stmts, $index);

    return array_merge($before, $values, $after);
}

/**
 * @param Node\Stmt[] $stmts
 * @param string      $target
 * @return int
 * @internal
 */
function injectionID(array $stmts, string $target): int
{
    foreach ($stmts as $index => $child) {
        if ($child instanceof $target) {
            return $index;
        }
    }

    return 0;
}
