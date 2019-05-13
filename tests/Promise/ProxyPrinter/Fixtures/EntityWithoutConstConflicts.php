<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests\ProxyPrinter\Fixtures;

class EntityWithoutConstConflicts
{
    public const    PUBLIC_CONST    = 1;
    protected const PROTECTED_CONST = 2;
    private const   PRIVATE_CONST   = 3;
}