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
 * @return bool
 */
function php74(): bool
{
    return version_compare(phpVersion(), '7.4.0', '>=');
}

/**
 * @return string
 */
function phpVersion(): string
{
    return PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.' . PHP_RELEASE_VERSION;
}
