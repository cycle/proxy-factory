<?php

/**
 * Spiral Framework. Cycle ProxyFactory
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */

declare(strict_types=1);

namespace Cycle\ORM\Promise;

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
 * Remove trailing digits in the given name.
 *
 * @param string $name
 * @param int    $number
 * @return string
 */
function trimTrailingDigits(string $name, int $number): string
{
    $pos = mb_strripos($name, (string)$number);
    if ($pos === false) {
        return $name;
    }

    return mb_substr($name, 0, $pos);
}

/**
 * Remove any kinds of php open tags.
 *
 * @param string $code
 * @return string
 */
function trimPHPOpenTag(string $code): string
{
    if (mb_strpos($code, '<?php') === 0) {
        return mb_substr($code, 5);
    }

    if (mb_strpos($code, '<?') === 0) {
        return mb_substr($code, 2);
    }

    return $code;
}


/**
 * Create short name (without namespaces).
 *
 * @param string $name
 * @return string
 */
function shortName(string $name): string
{
    $pos = mb_strrpos($name, '\\');
    if ($pos === false) {
        return $name;
    }

    return mb_substr($name, $pos + 1);
}
