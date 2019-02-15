<?php
declare(strict_types=1);

namespace Spiral\Cycle\Promise\Visitor;

use PhpParser\Comment\Doc;
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

        $node->setDocComment($this->makePHPDoc());
        $node->stmts = [new Node\Stmt\Return_($this->resolvedParentMethodCall($node))];

        return $node;
    }

    private function resolvedParentMethodCall(Node\Stmt\ClassMethod $node): Node\Expr\MethodCall
    {
        return new Node\Expr\MethodCall($this->resolverCall(), $node->name->name);
    }

    private function resolverCall(): Node\Expr\MethodCall
    {
        return new Node\Expr\MethodCall(new Node\Expr\Variable('this'), ProxyCreator::PROXY_RESOLVER_CALL);
    }

    private function makePHPDoc(): Doc
    {
        return new Doc($this->docLines());
    }

    private function docLines(): string
    {
        $lines = [
            "/**",
            " * {@inheritdoc}",
            " */"
        ];

        return join("\n", $lines);
    }
}