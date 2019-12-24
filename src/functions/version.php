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
 * @param string $v1
 * @param string $v2
 * @return bool
 */
function phpVersionBetween(string $v1, string $v2): bool
{
    return version_compare(phpVersion(), $v1, '>=') && version_compare(phpVersion(), $v2, '<');
}

/**
 * @return string
 */
function phpVersion(): string
{
    return PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.' . PHP_RELEASE_VERSION;
}
