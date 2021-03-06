<?php

/**
 * Spiral Framework. Cycle ProxyFactory
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */

declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests\ProxyPrinter\Methods\Fixtures;

class EntityWithMethods
{
    public function undefinedReturn()
    {
        if (true) {
            return false;
        }
    }

    public function public(): void
    {
    }

    public static function publicStatic(): void
    {
    }

    protected function protected(): void
    {
    }

    final protected function protectedFinal(): void
    {
    }

    private function private(): void
    {
    }
}
