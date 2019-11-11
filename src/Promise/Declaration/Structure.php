<?php

/**
 * Spiral Framework. Cycle ProxyFactory
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */

declare(strict_types=1);

namespace Cycle\ORM\Promise\Declaration;

use PhpParser\Node\Stmt\ClassMethod;

final class Structure
{
    /** @var string[] */
    public $constants = [];

    /** @var ClassMethod[] */
    public $methods = [];

    /** @var bool */
    public $hasClone;

    /** @var \SplObjectStorage */
    private $properties;

    /**
     * Structure constructor.
     */
    protected function __construct()
    {
    }

    /**
     * @param array             $constants
     * @param \SplObjectStorage $properties
     * @param bool              $hasClone
     * @param ClassMethod       ...$methods
     * @return Structure
     */
    public static function create(
        array $constants,
        \SplObjectStorage $properties,
        bool $hasClone,
        ClassMethod ...$methods
    ): Structure {
        $self = new self();
        $self->constants = $constants;
        $self->properties = $properties;
        $self->methods = $methods;
        $self->hasClone = $hasClone;

        return $self;
    }

    /**
     * @return string[]
     */
    public function toBeUnsetProperties(): array
    {
        $names = [];
        /** @var \ReflectionProperty $property */
        foreach ($this->properties as $property) {
            if ($this->properties[$property] === true && $property->isPublic()) {
                $names[] = $property->getName();
            }
        }

        return $names;
    }

    /**
     * @return string[]
     */
    public function publicProperties(): array
    {
        $names = [];
        /** @var \ReflectionProperty $property */
        foreach ($this->properties as $property) {
            if ($property->isPublic()) {
                $names[] = $property->getName();
            }
        }

        return $names;
    }

    /**
     * @return string[]
     */
    public function properties(): array
    {
        $names = [];
        /** @var \ReflectionProperty $property */
        foreach ($this->properties as $property) {
            $names[] = $property->getName();
        }

        return $names;
    }

    /**
     * @return array
     */
    public function methodNames(): array
    {
        $names = [];
        foreach ($this->methods as $method) {
            $names[] = $method->name->name;
        }

        return $names;
    }
}
