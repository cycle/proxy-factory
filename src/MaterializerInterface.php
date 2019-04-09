<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise;

interface MaterializerInterface
{
    public function materialize(string $code, string $shortClassName, \ReflectionClass $reflection): void;
}