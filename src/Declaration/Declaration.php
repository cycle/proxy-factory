<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Declaration;

class Declaration
{
    /** @var Proxy\Class_ */
    public $class;

    /** @var Proxy\Class_ */
    public $extends;

    public function __construct(string $extends, string $class)
    {
        $this->class = Proxy\Class_::create($this->extractClass($class), $this->extractExtendedNamespace($class, $extends));
        $this->extends = Proxy\Class_::create($this->extractClass($extends), $this->extractNamespace($extends));
    }

    private function extractClass(string $class): string
    {
        $lastPosition = mb_strripos($class, '\\');
        if ($lastPosition === false) {
            return $class;
        }

        return mb_substr($class, $lastPosition + 1);
    }

    private function extractExtendedNamespace(string $class, string $extends): ?string
    {
        $lastPosition = mb_strripos($class, '\\');
        if ($lastPosition === 0) {
            return null;
        }

        if ($lastPosition !== false) {
            return $this->extractNamespace($class);
        }

        return $this->extractNamespace($extends);
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