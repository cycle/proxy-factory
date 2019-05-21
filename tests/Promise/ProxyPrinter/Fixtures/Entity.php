<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests\ProxyPrinter\Fixtures;

abstract class Entity
{
    use TestTrait;
    const MY_CONST = 'value';

    public $public;
    public static $publicStatic;
    protected $protected;
    protected $__resolver;
    private $private = null;

    public function __construct()
    {
        //have some body
    }

    public function public(): ?string
    {
        return 'pub';
    }

    public function __resolver()
    {
    }

    /**
     * @return array
     */
    protected function protected(): array
    {
    }

    private function private()
    {
    }

    public static function publicStatic()
    {
    }

    public final function publicFinal()
    {
    }

    public function __toString()
    {
        return '';
    }
}