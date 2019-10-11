<?php

declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests\Fixtures;

abstract class Entity
{
    use EntityTrait;

    public const MY_CONST = 'value';

    public $public;
    public static $publicStatic;
    protected $protected;
    protected $__resolver;
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
