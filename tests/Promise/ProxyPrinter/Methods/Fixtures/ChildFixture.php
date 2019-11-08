<?php

declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests\ProxyPrinter\Methods\Fixtures;

class ChildFixture
{
    public function hasReturn()
    {
        return false;
    }

    public function conditionalReturn()
    {
        if (true) {
            return true;
        }
    }

    public function voidReturn(): void
    {
        $var = 'value';
        if ($var !== 'value') {
            return;
        }
    }

    public function setter($var): void
    {
    }

    public function getter()
    {
        return 'value';
    }

    public function childMethod(): void
    {
    }
}
