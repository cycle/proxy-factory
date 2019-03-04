<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise;

interface NamingInterface
{
    public function name(string $class): string;
}