<?php

/**
 * Spiral Framework.
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */
declare(strict_types=1);

namespace Cycle\ORM\Promise\Visitor;

use Cycle\ORM\Promise\Expressions;
use Cycle\ORM\Promise\PHPDoc;
use PhpParser\Builder\Param;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * Add parent call via proxy resolver
 */
final class AddProxiedMethods extends NodeVisitorAbstract
{
    /** @var string */
    private $resolverProperty;

    /** @var Node\Stmt\ClassMethod[] */
    private $methods;

    /** @var string */
    private $resolveMethod;

    /**
     * @param string $property
     * @param array  $methods
     * @param string $resolveMethod
     */
    public function __construct(string $property, array $methods, string $resolveMethod)
    {
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
            if ($method->name->name === 'undefinedReturn') {
//                print_r([__METHOD__ => $method->stmts]);
            }
            if ($method->name->name === '__clone') {
                $method->stmts = [$this->buildCloneExpression()];
                $node->stmts[] = $method;
            } elseif ($this->hasReturnStmt($method)) {
                $node->stmts[] = $this->modifyReturnMethod($method);
            } else {
                $node->stmts[] = $this->modifyExprMethod($method);
            }
        }

        return $node;
    }

    /**
     * @return Node\Stmt\Expression
     */
    private function buildCloneExpression(): Node\Stmt\Expression
    {
        return new Node\Stmt\Expression(
            new Node\Expr\Assign(
                Expressions::resolvePropertyFetch('this', $this->resolverProperty),
                new Node\Expr\Clone_(Expressions::resolvePropertyFetch('this', $this->resolverProperty))
            )
        );
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
                return true;
            }

            if ($this->findReturnStmt($stmt) === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Node\Stmt\ClassMethod $method
     * @return Node\Stmt\ClassMethod
     */
    private function modifyReturnMethod(Node\Stmt\ClassMethod $method): Node\Stmt\ClassMethod
    {
        $method->setDocComment(PHPDoc::writeInheritdoc());

        $resolved = Expressions::resolveMethodCall('this', $this->resolverProperty, $this->resolveMethod);
        $stmt = new Node\Stmt\Return_(new Node\Expr\MethodCall(
            $resolved,
            $method->name->name,
            $this->packMethodArgs($method)
        ));

        $method->stmts = [Expressions::throwExceptionOnNull($resolved, $stmt)];

        return $method;
    }

    /**
     * @param Node\Stmt\ClassMethod $method
     * @return Node\Stmt\ClassMethod
     */
    private function modifyExprMethod(Node\Stmt\ClassMethod $method): Node\Stmt\ClassMethod
    {
        $method->setDocComment(PHPDoc::writeInheritdoc());

        $resolved = Expressions::resolveMethodCall('this', $this->resolverProperty, $this->resolveMethod);
        $stmt = new Node\Stmt\Expression(
            new Node\Expr\MethodCall($resolved, $method->name->name, $this->packMethodArgs($method))
        );

        $method->stmts = [Expressions::throwExceptionOnNull($resolved, $stmt)];

        return $method;
    }

    /**
     * @param Node\Stmt\ClassMethod $method
     * @return array
     */
    private function packMethodArgs(Node\Stmt\ClassMethod $method): array
    {
        $args = [];
        /** @var \PhpParser\Node\Param $param */
        foreach ($method->getParams() as $param) {
            $args[] = (new Param($param->var->name))->getNode();
        }

        return $args;
    }
}
