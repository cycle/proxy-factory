<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise;

class Names
{
    public function make(\ReflectionClass $reflection, ?string $namespace = null): string
    {
        $hash = hash('sha256', $reflection->getName() . $reflection->getFileName());

        $name = "{$reflection->getShortName()}_$hash";
        if ($namespace !== null) {
            return "$namespace\\$name";
        }

        return $name;
    }
}