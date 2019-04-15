<?php

namespace Cycle\ORM\Promise\Materizalizer;

use Cycle\ORM\Promise\MaterializerInterface;
use Cycle\ORM\Promise\Utils;

final class EvalMaterializer implements MaterializerInterface
{
    /**
     * {@inheritdoc}
     * If class already exists - do nothing (prevent from memory leaking)
     */
    public function materialize(string $code, string $shortClassName, \ReflectionClass $reflection): void
    {
        eval(Utils::trimPHPOpenTag($code));
    }
}