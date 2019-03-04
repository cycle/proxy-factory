<?php

namespace Cycle\ORM\Promise\Materizalizer;

use Cycle\ORM\Promise\Declaration\Declaration;
use Cycle\ORM\Promise\MaterializerInterface;

class EvalMaterializer implements MaterializerInterface
{
    public function materialize(string $code, Declaration $declaration): void
    {
        if (class_exists($declaration->class->getNamespacesName())) {
            throw new \RuntimeException("Class `{$declaration->class->getNamespacesName()}` already exists.");
        }

        $output = ltrim($code, "<?php");

        eval($output);
    }
}