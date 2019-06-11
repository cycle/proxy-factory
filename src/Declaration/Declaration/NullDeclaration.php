<?php
/**
 * Spiral Framework.
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */
declare(strict_types=1);

namespace Cycle\ORM\Promise\Declaration\Declaration;

use Cycle\ORM\Promise\Declaration\DeclarationInterface;

class NullDeclaration implements DeclarationInterface
{
    public function getShortName(): ?string
    {
        return null;
    }

    public function getNamespaceName(): ?string
    {
        return null;
    }

    public function getFullName(): ?string
    {
        return null;
    }
}