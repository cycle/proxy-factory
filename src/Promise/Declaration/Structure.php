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
use ReflectionProperty;
use SplObjectStorage;

use function Cycle\ORM\Promise\phpVersionBetween;

final class Structure
{
    /** @var string[] */
    public $constants = [];

    /** @var ClassMethod[] */
    public $methods = [];

    /** @var bool */
    public $hasClone;

    /** @var SplObjectStorage */
    private $properties;

    /**
     * Structure constructor.
     */
    protected function __construct()
    {
    }

    /**
     * @param array            $constants
     * @param SplObjectStorage $properties
     * @param bool             $hasClone
     * @param ClassMethod      ...$methods
     * @return Structure
     */
    public static function create(
        array $constants,
        SplObjectStorage $properties,
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
     * A list of properties to be unset due to they are initialized (have a default value).
     * @return string[]
     */
    public function toBeUnsetProperties(): array
    {
        $names = [];
        /** @var ReflectionProperty $property */
        foreach ($this->properties as $property) {
            if ($this->doesDefaultMatter($property) && $property->isPublic()) {
                $names[] = $property->getName();
            }
        }

        return $names;
    }

    /**
     * A list of public properties. Any access to them be proxied.
     * @return string[]
     */
    public function publicProperties(): array
    {
        $names = [];
        /** @var ReflectionProperty $property */
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
        /** @var ReflectionProperty $property */
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

    /**
     * Since php7.4.1 the behaviour changed as it was before php7.4.0. All properties should be unset.
     * @see https://github.com/php/php-src/pull/4974
     * @param ReflectionProperty $property
     * @return bool
     */
    private function doesDefaultMatter(ReflectionProperty $property): bool
    {
        return !phpVersionBetween('7.4.0', '7.4.1') || $this->properties[$property] === true;
    }
}
