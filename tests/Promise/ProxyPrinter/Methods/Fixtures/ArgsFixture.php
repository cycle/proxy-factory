<?php

// phpcs:ignoreFile
declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests\ProxyPrinter\Methods\Fixtures;

class ArgsFixture
{
    public function referencedSetter(string $a, &$b, int $c): void
    {
    }

    public function typedSetter(string $a, $b, int $c): void
    {
    }


    public function defaultsSetter(string $a, $b = [], int $c = 3, bool $d): void
    {
    }

    public function variadicSetter($a, string ...$b): void
    {
    }
}
