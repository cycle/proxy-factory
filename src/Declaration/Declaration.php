<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Declaration;

class Declaration
{
    /** @var Proxy\ClassInterface */
    public $class;

    /** @var Proxy\ClassInterface */
    public $parent;

    public static function createFromReflection(\ReflectionClass $parent, string $class): self
    {
        $self = new self();
        $self->parent = new Proxy\ReflectionClass_($parent);
        $self->class = new Proxy\Class_($class, $self->parent);

        return $self;
    }
}