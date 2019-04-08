<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Declaration;

class Declaration
{
    /** @var Proxy\ClassInterface */
    public $class;

    /** @var Proxy\ClassInterface */
    public $parent;

    public function __construct(\ReflectionClass $parent, string $class)
    {
        $this->parent = new Proxy\ReflectionClass_($parent);
        $this->class = new Proxy\Class_($class, $this->parent);
    }
}