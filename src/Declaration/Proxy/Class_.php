<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Declaration\Proxy;

class Class_
{
    /** @var string */
    public $name;

    /** @var string|null */
    public $namespace;

    public static function create(string $name, ?string $namespace): Class_
    {
        $self = new self();
        $self->name = $name;
        $self->namespace = $namespace;

        return $self;
    }

    public function getNamespacesName(): string
    {
        if (empty($this->namespace)) {
            return $this->name;
        }

        return "{$this->namespace}\\{$this->name}";
    }

    private function __construct()
    {
    }
}