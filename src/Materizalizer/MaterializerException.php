<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Materizalizer;

class MaterializerException extends \RuntimeException
{
    public function __construct(string $className)
    {
        parent::__construct("Class `$className` already exists.");
    }
}