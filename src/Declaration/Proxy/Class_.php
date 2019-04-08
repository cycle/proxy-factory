<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Declaration\Proxy;

final class Class_ implements ClassInterface
{
    /** @var string */
    private $shortName;

    /** @var string|null */
    private $namespace;

    public function __construct(string $name, ClassInterface $parent)
    {
        $this->shortName = $this->makeShortName($name);
        $this->namespace = $this->makeNamespaceName($name, $parent);
    }

    public function getShortName(): string
    {
        return $this->shortName;
    }

    public function getNamespaceName(): ?string
    {
        return $this->namespace;
    }

    public function getFullName(): string
    {
        if (empty($this->namespace)) {
            return "\\{$this->shortName}";
        }

        return "{$this->namespace}\\{$this->shortName}";
    }

    private function makeShortName(string $class): string
    {
        $class = rtrim($class, '\\');
        $lastPosition = mb_strripos($class, '\\');
        if ($lastPosition === false) {
            return $class;
        }

        return mb_substr($class, $lastPosition + 1);
    }

    private function makeNamespaceName(string $class, ClassInterface $parent): ?string
    {
        $class = rtrim($class, '\\');
        $lastPosition = mb_strripos($class, '\\');
        if ($lastPosition === 0) {
            return null;
        }

        if ($lastPosition !== false) {
            return ltrim(mb_substr($class, 0, $lastPosition), '\\');
        }

        return $parent->getNamespaceName();
    }
}