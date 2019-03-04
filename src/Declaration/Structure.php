<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Declaration;

class Structure
{
    /** @var string[] */
    public $properties = [];

    /** @var \PhpParser\Node\Stmt\ClassMethod[] */
    public $methods = [];

    /** @var bool */
    public $hasConstructor;

    public static function create(array $properties, array $methods, bool $hasConstructor): Structure
    {
        $self = new self();
        $self->properties = $properties;
        $self->methods = $methods;
        $self->hasConstructor = $hasConstructor;

        return $self;
    }

    protected function __construct()
    {
    }
}