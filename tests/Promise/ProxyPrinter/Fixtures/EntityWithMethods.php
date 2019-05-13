<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests\ProxyPrinter\Fixtures;

class EntityWithMethods
{
    public function public(): void
    {
    }

    protected function protected(): void
    {
    }

    private function private()
    {
    }

    public static function publicStatic(): void
    {
    }

    final protected function protectedFinal(): void
    {
    }
}