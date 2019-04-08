<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Declaration\Extractor;

use PhpParser\Node;

final class Methods
{
    private const MAGIC_METHOD_NAMES = [
        '__construct',
        '__destruct',
        '__call',
        '__callstatic',
        '__get',
        '__set',
        '__isset',
        '__unset',
        '__sleep',
        '__wakeup',
        '__toString',
        '__invoke',
        '__set_state',
        '__clone',
        '__debuginfo',
    ];

    public function getMethods(\ReflectionClass $reflection): array
    {
        $methods = [];

        foreach ($reflection->getMethods() as $method) {
            if ($this->isIgnoredMethod($method)) {
                continue;
            }

            $flags = $this->makeNodeFlags($method);
            $returnType = $this->makeReturnType($method);

            $methods[] = new Node\Stmt\ClassMethod($method->getName(), compact('flags', 'returnType'));
        }

        return $methods;
    }

    private function isIgnoredMethod(\ReflectionMethod $method): bool
    {
        return $method->isPrivate() || $method->isStatic() || $method->isFinal() || $method->isAbstract() || $this->isMagicMethod($method->getName());
    }

    private function isMagicMethod(string $name): bool
    {
        return in_array($name, self::MAGIC_METHOD_NAMES, true);
    }

    private function makeNodeFlags(\ReflectionMethod $method): int
    {
        $flags = [];
        if ($method->isPublic()) {
            $flags[] = Node\Stmt\Class_::MODIFIER_PUBLIC;
        } elseif ($method->isProtected()) {
            $flags[] = Node\Stmt\Class_::MODIFIER_PROTECTED;
        }

        return array_reduce($flags, function ($a, $b) {
            return $a | $b;
        }, 0);
    }

    private function makeReturnType(\ReflectionMethod $method): ?Node
    {
        if (!$method->hasReturnType()) {
            return null;
        }

        $returnType = $method->getReturnType();

        if ($returnType === null) {
            return null;
        }

        $name = $returnType->getName();

        if (!$returnType->isBuiltin()) {
            $name = '\\' . $name;
        }

        if ($returnType->allowsNull()) {
            $name = '?' . $name;
        }

        return new Node\Identifier($name);
    }
}