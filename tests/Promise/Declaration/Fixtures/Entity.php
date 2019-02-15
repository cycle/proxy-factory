<?php
declare(strict_types=1);

namespace Spiral\Cycle\Promise\Tests\Declaration\Fixtures;

use Spiral\Cycle\Promise\ResolverTrait;

abstract class Entity
{
    use ResolverTrait;

    const MY_CONST = 'value';

    public $public;
    public static $publicStatic;
    protected $protected;
//    protected $__resolver;
    private $private = null;

    public function public()
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

    abstract protected function protectedAbstract();
}