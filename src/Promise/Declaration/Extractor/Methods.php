<?php

/**
 * Spiral Framework. Cycle ProxyFactory
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */

declare(strict_types=1);

namespace Cycle\ORM\Promise\Declaration\Extractor;

use Cycle\ORM\Promise\Traverser;
use Cycle\ORM\Promise\Visitor\Declaration\FetchMethods;
use PhpParser\Builder\Param;
use PhpParser\Node;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionType;

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

    private static $nodesByFileNameCache = [];

    /** @varTraverser */
    private $traverser;

    /** @var Parser */
    private $parser;

    /**
     * @param Traverser   $traverser
     * @param Parser|null $parser
     */
    public function __construct(
        Traverser $traverser,
        Parser $parser = null
    ) {
        $this->traverser = $traverser;
        $this->parser = $parser ?? (new ParserFactory())->create(ParserFactory::ONLY_PHP7);
    }

    /**
     * @param ReflectionClass $reflection
     * @return array
     * @throws ReflectionException
     */
    public function getMethods(ReflectionClass $reflection): array
    {
        $parents = [$reflection->name => $reflection];
        foreach ($reflection->getMethods() as $method) {
            $class = $method->getDeclaringClass();
            $parents[$class->name] = $class;
        }

        $methodNodes = $this->getExtendedMethodNodes($parents);
        $methods = [];

        foreach ($reflection->getMethods() as $method) {
            if ($this->isIgnoredMethod($method)) {
                continue;
            }

            $methods[] = new Node\Stmt\ClassMethod($method->name, [
                'flags'      => $this->packFlags($method),
                'returnType' => $this->defineReturnType($method),
                'params'     => $this->packParams($method),
                'byRef'      => $method->returnsReference(),
                'stmts'      => !empty($methodNodes[$method->name]) ? $methodNodes[$method->name]->stmts : []
            ]);
        }

        return $methods;
    }

    /**
     * @param ReflectionMethod $method
     * @return bool
     */
    private function isIgnoredMethod(ReflectionMethod $method): bool
    {
        return $method->isPrivate()
            || $method->isStatic()
            || $method->isFinal()
            || $method->isAbstract()
            || $this->isMagicMethod($method->name);
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
     * @param ReflectionMethod $method
     * @return int
     */
    private function packFlags(ReflectionMethod $method): int
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
     * @param ReflectionMethod $method
     * @return Node|null
     */
    private function defineReturnType(ReflectionMethod $method): ?Node
    {
        if (!$method->hasReturnType()) {
            return null;
        }

        $name = $this->defineType($method, $method->getReturnType());

        return $name !== null ? new Node\Identifier($name) : null;
    }

    /**
     * @param ReflectionMethod $method
     * @return array
     * @throws ReflectionException
     */
    private function packParams(ReflectionMethod $method): array
    {
        $params = [];
        foreach ($method->getParameters() as $parameter) {
            $param = new Param($parameter->name);

            if ($parameter->isDefaultValueAvailable()) {
                $param->setDefault($parameter->getDefaultValue());
            }

            if ($parameter->isVariadic()) {
                $param->makeVariadic();
            }

            if ($parameter->isPassedByReference()) {
                $param->makeByRef();
            }

            $type = $this->defineParamType($parameter, $method);
            if ($type !== null) {
                $param->setType($type);
            }

            $params[] = $param->getNode();
        }

        return $params;
    }

    /**
     * @param ReflectionParameter $parameter
     * @param ReflectionMethod    $method
     * @return string|null
     */
    private function defineParamType(ReflectionParameter $parameter, ReflectionMethod $method): ?string
    {
        if (!$parameter->hasType()) {
            return null;
        }

        return $this->defineType($method, $parameter->getType());
    }

    /**
     * @param ReflectionMethod    $method
     * @param ReflectionType|null $type
     * @return string|null
     */
    private function defineType(ReflectionMethod $method, ?ReflectionType $type): ?string
    {
        if ($type === null) {
            return null;
        }

        $name = $type->getName();
        $name = $this->replacedSelfTypeName($method, $name);

        if ($this->typeShouldBeQualified($type, $name)) {
            $name = '\\' . $name;
        }

        if ($type->allowsNull()) {
            $name = "?$name";
        }

        return $name;
    }

    /**
     * @param ReflectionMethod $method
     * @param string           $name
     * @return string
     */
    private function replacedSelfTypeName(ReflectionMethod $method, string $name): string
    {
        return $name === 'self' ? $method->class : $name;
    }

    /**
     * @param ReflectionType $returnType
     * @param string         $name
     * @return bool
     */
    private function typeShouldBeQualified(ReflectionType $returnType, string $name): bool
    {
        if (in_array($name, self::RESERVED_UNQUALIFIED_RETURN_TYPES, true)) {
            return false;
        }

        return !$returnType->isBuiltin();
    }

    /**
     * @param ReflectionClass[] $reflections
     * @return Node\Stmt\ClassMethod[]
     */
    private function getExtendedMethodNodes(array $reflections): array
    {
        $nodes = [];
        foreach ($reflections as $reflection) {
            if (!$fileName = $reflection->getFileName()) {
                continue;
            }

            foreach ($this->getMethodsByFile($fileName) as $name => $method) {
                if (!isset($nodes[$name])) {
                    $nodes[$name] = $method;
                }
            }
        }

        return $nodes;
    }

    /**
     * @param  string $fileName
     * @return array
     */
    private function getMethodsByFile(string $fileName): array
    {
        if (!array_key_exists($fileName, self::$nodesByFileNameCache)) {
            self::$nodesByFileNameCache[$fileName] = [];
            if (is_file($fileName)) {
                $methods = new FetchMethods();
                $this->traverser->traverse(
                    $this->parser->parse(
                        file_get_contents($fileName)
                    ),
                    $methods
                );

                self::$nodesByFileNameCache[$fileName] = $methods->getMethods();
            }
        }
        return self::$nodesByFileNameCache[$fileName];
    }

    public static function resetNodesCache(): void
    {
        self::$nodesByFileNameCache = [];
    }
}
