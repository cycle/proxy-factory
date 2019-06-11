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
    public function getShortName(): string;

    public function getNamespaceName(): ?string;

    public function getFullName(): string;
}