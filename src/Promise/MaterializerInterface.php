<?php

/**
 * Spiral Framework. Cycle ProxyFactory
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */
declare(strict_types=1);

namespace Cycle\ORM\Promise;

interface MaterializerInterface
{
    /**
     * @param string           $code
     * @param string           $shortClassName
     * @param \ReflectionClass $reflection
     */
    public function materialize(string $code, string $shortClassName, \ReflectionClass $reflection): void;
}
