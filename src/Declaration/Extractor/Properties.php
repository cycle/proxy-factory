<?php
/**
 * Spiral Framework.
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */
declare(strict_types=1);

namespace Cycle\ORM\Promise\Declaration\Extractor;

final class Properties
{
    public function getProperties(\ReflectionClass $reflection): array
    {
        $properties = [];

        foreach ($reflection->getProperties() as $property) {
            if ($this->isIgnoredProperty($property)) {
                continue;
            }

            $properties[] = $property->name;
        }

        return $properties;
    }

    private function isIgnoredProperty(\ReflectionProperty $property): bool
    {
        return $property->isPrivate() || $property->isStatic();
    }
}