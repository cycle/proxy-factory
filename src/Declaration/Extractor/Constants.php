<?php
declare(strict_types=1);


namespace Cycle\ORM\Promise\Declaration\Extractor;

final class Constants
{
    public function getConstants(\ReflectionClass $reflection): array
    {
        $properties = [];

        foreach ($reflection->getReflectionConstants() as $constant) {
            if ($constant->isPrivate()) {
                continue;
            }

            $properties[] = $constant->name;
        }

        return $properties;
    }
}