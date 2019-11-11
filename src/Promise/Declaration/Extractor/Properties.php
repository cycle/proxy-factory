<?php

/**
 * Spiral Framework. Cycle ProxyFactory
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */

declare(strict_types=1);

namespace Cycle\ORM\Promise\Declaration\Extractor;

final class Properties
{
    /**
     * @param \ReflectionClass $reflection
     * @return \SplObjectStorage
     */
    public function getProperties(\ReflectionClass $reflection): \SplObjectStorage
    {
        $properties = new \SplObjectStorage();
        $defaults = $reflection->getDefaultProperties();

        foreach ($reflection->getProperties() as $property) {
            if ($this->isIgnoredProperty($property)) {
                continue;
            }

            $properties->attach($property, array_key_exists($property->getName(), $defaults));
        }

        return $properties;
    }

    /**
     * @param \ReflectionProperty $property
     * @return bool
     */
    private function isIgnoredProperty(\ReflectionProperty $property): bool
    {
        return $property->isPrivate() || $property->isStatic();
    }
}
