<?php

/**
 * Spiral Framework. Cycle ProxyFactory
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */

declare(strict_types=1);

namespace Cycle\ORM\Promise\Visitor;

use Cycle\ORM\Promise\PHPDoc;
use PhpParser\Builder\Param;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

use function Cycle\ORM\Promise\exprClone;
use function Cycle\ORM\Promise\resolveMethodCall;
use function Cycle\ORM\Promise\throwExceptionOnNull;

/**
 * Add parent call via proxy resolver
 */
final class AddProxiedMethods extends NodeVisitorAbstract
{
    /** @var string */
    private $class;

    /** @var string */
    private $resolverProperty;

    /** @var Node\Stmt\ClassMethod[] */
    private $methods;

    /** @var string */
    private $resolveMethod;

    /**
     * @param string $class
     * @param string $property
     * @param array  $methods
     * @param string $resolveMethod
     */
    public function __construct(string $class, string $property, array $methods, string $resolveMethod)
    {
        $this->class = $class;
        $this->resolverProperty = $property;
        $this->methods = $methods;
        $this->resolveMethod = $resolveMethod;
    }

    /**
     * {@inheritdoc}
     */
    public function leaveNode(Node $node)
    {
        if (!$node instanceof Node\Stmt\Class_) {
            return null;
        }

        foreach ($this->methods as $method) {
            if ($method->name->name === '__clone') {
                $method->stmts = [exprClone($this->resolverProperty)];
                $node->stmts[] = $method;
            } else {
                $node->stmts[] = $this->modifyMethodMethod(
                    $method,
                    $this->hasReturnStmt($method) ? Node\Stmt\Return_::class : Node\Stmt\Expression::class
                );
            }
        }

        return $node;
    }

    /**
     * @param Node\Stmt\ClassMethod $method
     * @return bool
     */
    private function hasReturnStmt(Node\Stmt\ClassMethod $method): bool
    {
        if ($method->returnType === 'void') {
            return false;
        }

        if ($method->returnType === null) {
            return $this->findReturnStmt($method);
        }

        if ($method->returnType instanceof Node\NullableType) {
            return true;
        }

        return $method->returnType instanceof Node\Identifier && $method->returnType->name !== 'void';
    }

    /**
     * @param Node\Stmt|Node\Stmt\ClassMethod $node
     * @return bool
     */
    private function findReturnStmt(Node\Stmt $node): bool
    {
        if (!property_exists($node, 'stmts') || !is_array($node->stmts)) {
            return false;
        }

        foreach ($node->stmts as $stmt) {
            if ($stmt instanceof Node\Stmt\Return_) {
                return $stmt->expr !== null;
            }

            if ($this->findReturnStmt($stmt) === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Node\Stmt\ClassMethod $method
     * @param string                $stmtWrapper
     * @return Node\Stmt\ClassMethod
     */
    private function modifyMethodMethod(Node\Stmt\ClassMethod $method, string $stmtWrapper): Node\Stmt\ClassMethod
    {
        $resolved = resolveMethodCall('this', $this->resolverProperty, $this->resolveMethod);
        $methodCall = new Node\Expr\MethodCall($resolved, $method->name->name, $this->packMethodArgs($method));

        $method->setDocComment(PHPDoc::writeInheritdoc());
        $method->stmts = [
            throwExceptionOnNull(
                $resolved,
                new $stmtWrapper($methodCall),
                'Method `%s()` not loaded for `%s`',
                [
                    $method->name->name,
                    $this->class
                ]
            )
        ];

        return $method;
    }

    /**
     * @param Node\Stmt\ClassMethod $method
     * @return array
     */
    private function packMethodArgs(Node\Stmt\ClassMethod $method): array
    {
        $args = [];
        /** @var Node\Param $param */
        foreach ($method->getParams() as $param) {
            $args[] = (new Param($param->var->name))->getNode();
        }

        return $args;
    }
}
