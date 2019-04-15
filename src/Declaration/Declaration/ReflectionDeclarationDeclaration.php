<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Declaration\Declaration;

use Cycle\ORM\Promise\Declaration\DeclarationInterface;

final class ReflectionDeclarationDeclaration implements DeclarationInterface
{
    /** @var \ReflectionClass */
    private $reflection;

    public function __construct(\ReflectionClass $class)
    {
        $this->reflection = $class;
    }

    public function getShortName(): string
    {
        return $this->reflection->getShortName();
    }

    public function getNamespaceName(): ?string
    {
        return $this->reflection->inNamespace() ? $this->reflection->getNamespaceName() : null;
    }

    public function getFullName(): string
    {
        return $this->reflection->getName();
    }
}