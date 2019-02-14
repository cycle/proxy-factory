<?php

namespace Spiral\Cycle\Promise\Declaration;

class Declaration
{
    /** @var string[] */
    public $properties = [];

    /** @var string[] */
    public $methods = [];

    public static function create(array $properties, array $methods): Declaration
    {
        $self = new self();
        $self->properties = $properties;
        $self->methods = $methods;

        return $self;
    }

    protected function __construct()
    {
    }
}