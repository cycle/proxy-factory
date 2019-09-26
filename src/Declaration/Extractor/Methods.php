<?php
/**
 * Spiral Framework.
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */
declare(strict_types=1);

namespace Cycle\ORM\Promise\Declaration\Extractor;

use PhpParser\Builder\Param;
use PhpParser\Node;

final class Methods
{
    private const MAGIC_METHODS = [
        '__construct',
        '__destruct',
        '__call',
        '__callStatic',
        '__get',
        '__set',
        '__isset',
        '__unset',
        '__sleep',
        '__wakeup',
        '__toString',
        '__invoke',
        '__set_state',
        '__debugInfo',
    ];

    private const RESERVED_UNQUALIFIED_RETURN_TYPES = ['self', 'static', 'object'];

    /**
     * @param \ReflectionClass $reflection
     * @return array
     */
    public function getMethods(\ReflectionClass $reflection): array
    {
        $methods = [];

        foreach ($reflection->getMethods() as $method) {
            if ($this->isIgnoredMethod($method)) {
                continue;
            }

            $methods[] = new Node\Stmt\ClassMethod($method->name, [
                'flags'      => $this->packFlags($method),
                'returnType' => $this->defineReturnType($method),
                'params'     => $this->packParams($method),
                'byRef'      => $method->returnsReference()
            ]);
        }

        return $methods;
    }

    /**
     * @param \ReflectionMethod $method
     * @return bool
     */
    private function isIgnoredMethod(\ReflectionMethod $method): bool
    {
        return $method->isPrivate() || $method->isStatic() || $method->isFinal() || $method->isAbstract() || $this->isMagicMethod($method->name);
    }

    /**
     * @param string $name
     * @return bool
     */
    private function isMagicMethod(string $name): bool
    {
        return in_array($name, self::MAGIC_METHODS, true);
    }

    /**
     * @param \ReflectionMethod $method
     * @return int
     */
    private function packFlags(\ReflectionMethod $method): int
    {
        $flags = [];
        if ($method->isPublic()) {
            $flags[] = Node\Stmt\Class_::MODIFIER_PUBLIC;
        } elseif ($method->isProtected()) {
            $flags[] = Node\Stmt\Class_::MODIFIER_PROTECTED;
        }

        return array_reduce($flags, static function ($a, $b) {
            return $a | $b;
        }, 0);
    }

    /**
     * @param \ReflectionMethod $method
     * @return Node|null
     */
    private function defineReturnType(\ReflectionMethod $method): ?Node
    {
        if (!$method->hasReturnType()) {
            return null;
        }

        $returnType = $method->getReturnType();

        if ($returnType === null) {
            return null;
        }

        $name = $returnType->getName();
        $name = $this->replacedSelfReturnTypeName($method, $name);

        if ($this->returnTypeShouldBeQualified($returnType, $name)) {
            $name = '\\' . $name;
        }

        if ($returnType->allowsNull()) {
            $name = '?' . $name;
        }

        return new Node\Identifier($name);
    }

    /**
     * @param \ReflectionMethod $method
     * @param string            $name
     * @return string
     */
    private function replacedSelfReturnTypeName(\ReflectionMethod $method, string $name): string
    {
        return $name === 'self' ? $method->getDeclaringClass()->getName() : $name;
    }

    /**
     * @param \ReflectionType $returnType
     * @param string          $name
     * @return bool
     */
    private function returnTypeShouldBeQualified(\ReflectionType $returnType, string $name): bool
    {
        if (in_array($name, self::RESERVED_UNQUALIFIED_RETURN_TYPES, true)) {
            return false;
        }

        return !$returnType->isBuiltin();
    }

    /**
     * @param \ReflectionMethod $method
     * @return array
     */
    private function packParams(\ReflectionMethod $method): array
    {
        $params = [];
        foreach ($method->getParameters() as $parameter) {
            $param = new Param($parameter->name);

            $type = $this->defineParamReturnType($parameter);
            if ($type !== null) {
                $param->setType($type);
            }

            $params[] = $param->getNode();
        }

        return $params;
    }

    /**
     * @param \ReflectionParameter $parameter
     * @return string|null
     */
    private function defineParamReturnType(\ReflectionParameter $parameter): ?string
    {
        if (!$parameter->hasType()) {
            return null;
        }

        $typeReflection = $parameter->getType();
        if ($typeReflection === null) {
            return null;
        }

        $type = $typeReflection->getName();
        if ($typeReflection->allowsNull()) {
            $type = "?$type";
        }

        return $type;
    }
}