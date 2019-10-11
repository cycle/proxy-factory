<?php

declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests\ProxyPrinter\Fixtures;

class ChildEntityWithMethods extends EntityWithMethods
{
    public function anotherPublic(): void
    {
    }

    public function undefinedReturn2()
    {
        if (true) {
            return false;
        }

        return '';
    }
}
