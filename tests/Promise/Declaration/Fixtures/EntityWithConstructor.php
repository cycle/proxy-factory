<?php
declare(strict_types=1);

namespace Spiral\Cycle\Promise\Tests\Declaration\Fixtures;

class EntityWithConstructor
{
    public $public;
    public static $publicStatic;
    protected $protected;
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

    public function __construct()
    {
    }
}