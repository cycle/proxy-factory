<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Declaration\Proxy;

class Class_
{
    /** @var string */
    public $class;

    /** @var string|null */
    public $namespace;

    public static function create(string $class, ?string $namespace): Class_
    {
        $self = new self();
        $self->class = $class;
        $self->namespace = $namespace;

        return $self;
    }

    public function getNamespacesName(): string
    {
        if (empty($this->namespace)) {
            return $this->class;
        }

        return "{$this->namespace}\\{$this->class}";
    }

    private function __construct()
    {
    }
}