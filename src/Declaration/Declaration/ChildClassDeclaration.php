<?php
/**
 * Spiral Framework.
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */
declare(strict_types=1);

namespace Cycle\ORM\Promise\Declaration\Declaration;

use Cycle\ORM\Promise\Declaration\DeclarationInterface;

final class ChildClassDeclaration implements DeclarationInterface
{
    /** @var string */
    private $shortName;

    /** @var string|null */
    private $namespace;

    /**
     * @param string               $name
     * @param DeclarationInterface $parent
     */
    public function __construct(string $name, DeclarationInterface $parent)
    {
        $this->shortName = $this->makeShortName($name);
        $this->namespace = $this->makeNamespaceName($name, $parent);
    }

    /**
     * @return string
     */
    public function getShortName(): string
    {
        return $this->shortName;
    }

    /**
     * @return string|null
     */
    public function getNamespaceName(): ?string
    {
        return $this->namespace;
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        if (empty($this->namespace)) {
            return "\\{$this->shortName}";
        }

        return "{$this->namespace}\\{$this->shortName}";
    }

    /**
     * @param string $class
     * @return string
     */
    private function makeShortName(string $class): string
    {
        $class = rtrim($class, '\\');
        $lastPosition = mb_strripos($class, '\\');
        if ($lastPosition === false) {
            return $class;
        }

        return mb_substr($class, $lastPosition + 1);
    }

    /**
     * @param string               $class
     * @param DeclarationInterface $parent
     * @return string|null
     */
    private function makeNamespaceName(string $class, DeclarationInterface $parent): ?string
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