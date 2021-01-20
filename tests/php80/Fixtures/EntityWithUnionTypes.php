<?php

/**
 * Spiral Framework. Cycle ProxyFactory
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */

declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests80\Fixtures;

class EntityWithUnionTypes
{
    public \Exception | \Error $throwable;

    public function method(\Exception | \Error $arg): \Exception | \Error
    {
        return $arg;
    }
}
