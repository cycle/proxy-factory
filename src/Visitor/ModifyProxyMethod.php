<?php
declare(strict_types=1);

namespace Spiral\Cycle\Promise\Visitor;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use Spiral\Cycle\Promise\ProxyCreator;

/**
 * Modify all accessible methods
 */
class ModifyProxyMethod extends NodeVisitorAbstract
{
    /**
     * {@inheritdoc}
     */
    public function leaveNode(Node $node)
    {
        if (!$node instanceof Node\Stmt\ClassMethod) {
            return null;
        }

        if ($node->isPrivate() || $node->isStatic() || $node->isFinal() || $node->isAbstract() || $node->isMagic()) {
            return NodeTraverser::REMOVE_NODE;
        }

        return new Node\Stmt\Return_($this->resolvedParentMethodCall($node));
    }

    private function resolvedParentMethodCall(Node\Stmt\ClassMethod $node): Node\Expr\MethodCall
    {
        return new Node\Expr\MethodCall($this->resolverCall(), $node->name->name);
    }

    private function resolverCall(): Node\Expr\MethodCall
    {
        return new Node\Expr\MethodCall(new Node\Expr\Variable('this'), ProxyCreator::PROXY_RESOLVER_CALL);
    }
}