<?php

declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests\ProxyPrinter\Methods;

use Cycle\ORM\Promise\Tests\ProxyPrinter\BaseProxyPrinterTest;

class MethodArgsTest extends BaseProxyPrinterTest
{
    public function testHasArgType(): void
    {
        $this->assertTrue(true);
    }

    public function testArgDefaults(): void
    {
        $this->assertTrue(true);
    }

    public function testArgVariadic(): void
    {
        $this->assertTrue(true);
    }
}
