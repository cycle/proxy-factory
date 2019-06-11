<?php
/**
 * Spiral Framework.
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */
declare(strict_types=1);

namespace Cycle\ORM\Promise;

interface MaterializerInterface
{
    public function materialize(string $code, ?string $shortClassName, ?\ReflectionClass $reflection): void;
}