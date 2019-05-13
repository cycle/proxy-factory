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
    private $property;

    /** @var Node\Stmt\ClassMethod[] */
    private $methods;

    /** @var string */
    private $resolveMethod;

    public function __construct(string $property, array $methods, string $resolveMethod)
    {
        $this->property = $property;
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
            } else {
                $node->stmts[] = $this->modifyMethod($method);
            }
        }

        return $node;
    }

    private function buildCloneExpression(): Node\Stmt\Expression
    {
        return new Node\Stmt\Expression(
            new Node\Expr\Assign(
                Expressions::resolvePropertyFetch('this', $this->property),
                new Node\Expr\Clone_(Expressions::resolvePropertyFetch('this', $this->property))
            )
        );
    }

    private function modifyMethod(Node\Stmt\ClassMethod $method): Node\Stmt\ClassMethod
    {
        $method->setDocComment(PHPDoc::writeInheritdoc());

        $stmts = new Node\Expr\MethodCall(
            Expressions::resolveMethodCall('this', $this->property, $this->resolveMethod),
            $method->name->name,
            $this->packMethodArgs($method)
        );

        if ($this->hasReturnStmt($method)) {
            $method->stmts = [new Node\Stmt\Return_($stmts)];
        } else {
            $method->stmts = [new Node\Stmt\Expression($stmts)];
        }

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