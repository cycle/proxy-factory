<?php

/**
 * Spiral Framework. Cycle ProxyFactory
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */

declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests80\Fixtures;

class EntityWithMixedArg
{
    public function method(mixed $arg): mixed
    {
        return $arg;
    }
}
