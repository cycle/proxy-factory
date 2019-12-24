<?php

/**
 * Spiral Framework. Cycle ProxyFactory
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */

declare(strict_types=1);

namespace Cycle\ORM\Promise\Declaration;

use ReflectionClass;

final class Declarations
{
    /**
     * @param ReflectionClass $parent
     * @return DeclarationInterface
     */
    public static function createParentFromReflection(ReflectionClass $parent): DeclarationInterface
    {
        return new Declaration\ReflectionDeclaration($parent);
    }

    /**
     * @param string               $class
     * @param DeclarationInterface $parent
     * @return DeclarationInterface
     */
    public static function createClassFromName(string $class, DeclarationInterface $parent): DeclarationInterface
    {
        return new Declaration\ChildClassDeclaration($class, $parent);
    }
}
