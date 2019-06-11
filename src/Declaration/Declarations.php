<?php
/**
 * Spiral Framework.
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */
declare(strict_types=1);

namespace Cycle\ORM\Promise\Declaration;

final class Declarations
{
    public static function createParentFromReflection(\ReflectionClass $parent): DeclarationInterface
    {
        return new Declaration\ReflectionDeclaration($parent);
    }

    public static function createClassFromName(string $class, DeclarationInterface $parent): DeclarationInterface
    {
        return new Declaration\ChildClassDeclaration($class, $parent);
    }
}