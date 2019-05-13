<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests\ProxyPrinter\Fixtures;

class EntityWithConstConflicts extends ConflictEntity
{
    public const    PUBLIC_CONST     = 1;
    protected const PROTECTED_CONST  = 2;
    private const   PRIVATE_CONST    = 3;
}