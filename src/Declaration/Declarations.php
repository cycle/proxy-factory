<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Declaration;

class Declarations
{
    /** @var DeclarationInterface */
    public $class;

    /** @var DeclarationInterface */
    public $parent;

    public static function createFromReflection(\ReflectionClass $parent, string $class): self
    {
        $self = new self();
        $self->parent = new Declaration\ReflectionDeclarationDeclaration($parent);
        $self->class = new Declaration\DeclarationDeclaration($class, $self->parent);

        return $self;
    }
}