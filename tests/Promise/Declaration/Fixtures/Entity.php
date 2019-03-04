<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests\Declaration\Fixtures;

class Entity
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
}