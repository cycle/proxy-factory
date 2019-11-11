<?php

/**
 * Spiral Framework. Cycle ProxyFactory
 *
 * @license MIT
 * @author  Valentin V (vvval)
 */

declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests\Declaration\Fixtures;

class EntityWithConstructor
{
    public $public;
    public static $publicStatic;
    protected $protected;
    private $private = null;

    public function __construct()
    {
    }

    public function __toString()
    {
        return '';
    }

    public function public(): void
    {
    }

    public static function publicStatic(): void
    {
    }

    final public function publicFinal(): void
    {
    }

    /**
     * @return array
     */
    protected function protected(): array
    {
    }

    private function private(): void
    {
    }
}
