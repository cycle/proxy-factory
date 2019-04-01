<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Declaration;

class Declaration
{
    /** @var Proxy\Class_ */
    public $class;

    /** @var Proxy\Class_ */
    public $parent;

    public function __construct(string $parent, string $class)
    {
        $this->parent = Proxy\Class_::create($this->extractClassName($parent), $this->extractNamespace($parent));
        $this->class = Proxy\Class_::create($this->extractClassName($class), $this->extractNamespaceOfParentClass($class, $parent));
    }

    private function extractClassName(string $class): string
    {
        $lastPosition = mb_strripos($class, '\\');
        if ($lastPosition === false) {
            return $class;
        }

        return mb_substr($class, $lastPosition + 1);
    }

    private function extractNamespaceOfParentClass(string $class, string $parent): ?string
    {
        $lastPosition = mb_strripos($class, '\\');
        if ($lastPosition === 0) {
            return null;
        }

        if ($lastPosition !== false) {
            return $this->extractNamespace($class);
        }

        return $this->extractNamespace($parent);
    }

    private function extractNamespace(string $class): ?string
    {
        $lastPosition = mb_strripos($class, '\\');
        if ($lastPosition === false || $lastPosition === 0) {
            return null;
        }

        return mb_substr($class, 0, $lastPosition);
    }
}