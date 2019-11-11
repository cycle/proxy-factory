<?php

/**
 * Spiral Framework. Cycle ProxyFactory
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */

declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests\ProxyPrinter\Fixtures;

class EntityWithoutConstConflicts
{
    public const    PUBLIC_CONST    = 1;
    protected const PROTECTED_CONST = 2;
    private const   PRIVATE_CONST   = 3;

    public $publicProperty;
    public $publicPropertyWithDefaults = 'defaultPublicValue';
    protected $protectedProperty;
    protected $protectedPropertyWithDefaults = 'defaultProtectedValue';
}
