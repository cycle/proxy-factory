<?php

// phpcs:ignoreFile
declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests\ProxyPrinter\Methods\Fixtures;

use Cycle\ORM\Promise\Tests\ProxyPrinter;
use Cycle\ORM\Promise\Tests\ProxyPrinter\Methods\Fixtures\ChildFixture as Child;

class ArgsFixture
{
    public function referencedSetter(string $a, &$b, int $c): void
    {
    }

    public function typedSetter(string $a, $b, int $c): void
    {
    }


    public function defaultsSetter(string $a, $b = [], int $c = 3, ?bool $d = null): void
    {
    }

    public function variadicSetter($a, string ...$b): void
    {
    }

    public function shortNameSetter(ChildFixture $child): ChildFixture
    {
        return new Child();
    }

    public function halfNameSetter(ProxyPrinter\Methods\Fixtures\ChildFixture $child): ProxyPrinter\Methods\Fixtures\ChildFixture
    {
        return new Child();
    }

    public function longNameSetter(\Cycle\ORM\Promise\Tests\ProxyPrinter\Methods\Fixtures\ChildFixture $child): \Cycle\ORM\Promise\Tests\ProxyPrinter\Methods\Fixtures\ChildFixture
    {
        return new Child();
    }

    public function aliasNameSetter(Child $child): ?Child
    {
        return new Child();
    }

    public function selfSetter(self $child): void
    {
    }
}
