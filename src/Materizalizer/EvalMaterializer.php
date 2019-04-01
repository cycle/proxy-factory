<?php

namespace Cycle\ORM\Promise\Materizalizer;

use Cycle\ORM\Promise\Declaration\Declaration;
use Cycle\ORM\Promise\MaterializerInterface;

class EvalMaterializer implements MaterializerInterface
{
    /**
     * {@inheritdoc}
     * If class already exists - do nothing (prevent from memory leaking)
     */
    public function materialize(string $code, Declaration $declaration): void
    {
        if (class_exists($declaration->class->getNamespacesName())) {
            return;
        }

        eval(ltrim($code, '<?php'));
    }
}