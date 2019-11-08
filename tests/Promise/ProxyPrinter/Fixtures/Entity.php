<?php

/**
 * Spiral Framework. Cycle ProxyFactory
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */

declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests\ProxyPrinter\Fixtures;

abstract class Entity
{
    use TestTrait;

    public const MY_CONST = 'value';

    public $public;
    public static $publicStatic;
    protected $protected;
    protected $resolver;
    private $private = null;

    public function __construct()
    {
        //have some body
    }

    public function __resolver(): void
    {
    }

    public function __toString()
    {
        return '';
    }

    public function public(): ?string
    {
        return 'pub';
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
