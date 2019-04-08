<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise;

class ProxyFactoryException extends \Exception
{
    public static function wrap(\Throwable $e): self
    {
        return new self($e->getMessage(), $e->getCode(), $e);
    }
}