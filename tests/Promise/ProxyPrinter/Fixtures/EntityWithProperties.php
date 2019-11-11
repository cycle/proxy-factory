<?php

/**
 * Spiral Framework. Cycle ProxyFactory
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */

declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests\ProxyPrinter\Fixtures;

class EntityWithProperties
{
    public $publicProperty;
    public $publicPropertyWithDefaults = 'defaultPublicValue';
    protected $protectedProperty;
    protected $protectedPropertyWithDefaults = 'defaultProtectedValue';
}
