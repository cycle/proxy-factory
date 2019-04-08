<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise;

use Cycle\ORM\Promise\Declaration\Declaration;

interface MaterializerInterface
{
    public function materialize(string $code, Declaration $declaration, \ReflectionClass $reflection): void;
}