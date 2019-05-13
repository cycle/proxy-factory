<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Declaration;

class Structure
{
    /** @var string[] */
    public $properties = [];

    /** @var string[] */
    public $constants = [];

    /** @var \PhpParser\Node\Stmt\ClassMethod[] */
    public $methods = [];

    /** @var bool */
    public $hasConstructor;

    /** @var bool */
    public $hasClone;

    public static function create(array $constants, array $properties, array $methods, bool $hasConstructor, bool $hasClone): Structure
    {
        $self = new self();
        $self->constants = $constants;
        $self->properties = $properties;
        $self->methods = $methods;
        $self->hasConstructor = $hasConstructor;
        $self->hasClone = $hasClone;

        return $self;
    }

    protected function __construct()
    {
    }
}