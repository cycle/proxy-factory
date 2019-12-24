<?php

/**
 * Spiral Framework. Cycle ProxyFactory
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */

declare(strict_types=1);

namespace Cycle\ORM\Promise\Exception;

use Exception;
use Throwable;

class ProxyFactoryException extends Exception
{
    /**
     * @param Throwable $e
     * @return ProxyFactoryException
     */
    public static function wrap(Throwable $e): self
    {
        return new self($e->getMessage(), $e->getCode(), $e);
    }
}
