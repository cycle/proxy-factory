<?php
/**
 * Spiral Framework.
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */
declare(strict_types=1);

namespace Cycle\ORM\Promise\Declaration;

final class Structure
{
    /** @var string[] */
    public $properties = [];

    /** @var string[] */
    public $constants = [];

    /** @var \PhpParser\Node\Stmt\ClassMethod[] */
    public $methods = [];

    /** @var bool */
    public $hasClone;

    /**
     * @param array $constants
     * @param array $properties
     * @param array $methods
     * @param bool  $hasClone
     * @return Structure
     */
    public static function create(array $constants, array $properties, array $methods, bool $hasClone): Structure
    {
        $self = new self();
        $self->constants = $constants;
        $self->properties = $properties;
        $self->methods = $methods;
        $self->hasClone = $hasClone;

        return $self;
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
     * Structure constructor.
     */
    protected function __construct()
    {
    }
}