<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Naming;

use Cycle\ORM\Promise\NamingInterface;

final class HashPostfixNaming implements NamingInterface
{
    public function name(\ReflectionClass $reflection): string
    {
        return "{$reflection->getShortName()}_{$reflection->getName()}{$reflection->getFileName()}";
    }
}