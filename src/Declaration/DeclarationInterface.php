<?php

/**
 * Spiral Framework.
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */
declare(strict_types=1);

namespace Cycle\ORM\Promise\Declaration;

interface DeclarationInterface
{
    /**
     * @return string
     */
    public function getShortName(): string;

    /**
     * @return string|null
     */
    public function getNamespaceName(): ?string;

    /**
     * @return string
     */
    public function getFullName(): string;
}
