<?php
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
class AddProxiedMethods extends NodeVisitorAbstract
{
    /** @var string */
    private $resolverProperty;

    /** @var Node\Stmt\ClassMethod[] */
    private $methods;

    /** @var string */
    private $resolveMethod;

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

    private function buildCloneExpression(): Node\Stmt\Expression
    {
        return new Node\Stmt\Expression(
            new Node\Expr\Assign(
                Expressions::resolvePropertyFetch('this', $this->resolverProperty),
                new Node\Expr\Clone_(Expressions::resolvePropertyFetch('this', $this->resolverProperty))
            )
        );
    }

    private function modifyReturnMethod(Node\Stmt\ClassMethod $method): Node\Stmt\ClassMethod
    {
        $method->setDocComment(PHPDoc::writeInheritdoc());

        $resolved = Expressions::resolveMethodCall('this', $this->resolverProperty, $this->resolveMethod);
        $stmt = new Node\Stmt\Return_(new Node\Expr\MethodCall($resolved, $method->name->name, $this->packMethodArgs($method)));

        $method->stmts[] = Expressions::throwExceptionOnNull($resolved, $stmt);

        return $method;
    }

    private function modifyExprMethod(Node\Stmt\ClassMethod $method): Node\Stmt\ClassMethod
    {
        $method->setDocComment(PHPDoc::writeInheritdoc());

        $resolved = Expressions::resolveMethodCall('this', $this->resolverProperty, $this->resolveMethod);
        $stmt = new Node\Stmt\Expression(new Node\Expr\MethodCall($resolved, $method->name->name, $this->packMethodArgs($method)));

        $method->stmts[] = Expressions::throwExceptionOnNull($resolved, $stmt);

        return $method;
    }

    private function packMethodArgs(Node\Stmt\ClassMethod $method): array
    {
        $args = [];
        /** @var \PhpParser\Node\Param $param */
        foreach ($method->getParams() as $param) {
            $args[] = (new Param($param->var->name))->getNode();
        }

        return $args;
    }

    private function hasReturnStmt(Node\Stmt\ClassMethod $method): bool
    {
        if ($method->returnType === null || $method->returnType === 'void') {
            return false;
        }

        if ($method->returnType instanceof Node\NullableType) {
            return true;
        }

        return $method->returnType instanceof Node\Identifier && $method->returnType->name !== 'void';
    }
}