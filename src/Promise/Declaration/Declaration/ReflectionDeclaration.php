<?php

/**
 * Spiral Framework. Cycle ProxyFactory
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */

declare(strict_types=1);

namespace Cycle\ORM\Promise\Declaration\Declaration;

use Cycle\ORM\Promise\Declaration\DeclarationInterface;

final class ReflectionDeclaration implements DeclarationInterface
{
    /** @var \ReflectionClass */
    private $reflection;

    /**
     * @param \ReflectionClass $class
     */
    public function __construct(\ReflectionClass $class)
    {
        $this->reflection = $class;
    }

    /**
     * @return string
     */
    public function getShortName(): string
    {
        return $this->reflection->getShortName();
    }

    /**
     * @return string|null
     */
    public function getNamespaceName(): ?string
    {
        return $this->reflection->inNamespace() ? $this->reflection->getNamespaceName() : null;
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return $this->reflection->name;
    }
}
