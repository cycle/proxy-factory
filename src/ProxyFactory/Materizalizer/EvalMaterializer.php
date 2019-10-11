<?php

/**
 * Spiral Framework. Cycle ProxyFactory
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */
declare(strict_types=1);

namespace Cycle\ORM\Promise\Materizalizer;

use Cycle\ORM\Promise\MaterializerInterface;

use function Cycle\ORM\Promise\trimPHPOpenTag;

final class EvalMaterializer implements MaterializerInterface
{
    /**
     * {@inheritdoc}
     * If class already exists - do nothing (prevent from memory leaking)
     */
    public function materialize(string $code, string $shortClassName, \ReflectionClass $reflection): void
    {
        eval(trimPHPOpenTag($code));
    }
}
