<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * Modify all accessible methods
 */
class UpdatePromiseMethods extends NodeVisitorAbstract
{
    /** @var string */
    private $property;

    public function __construct(string $property)
    {
        $this->property = $property;
    }

    /**
     * {@inheritdoc}
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\ClassMethod && !$this->ignoreMethod($node)) {
            $node->stmts = [new Node\Stmt\Return_($this->resolvedParentMethodCall($node))];
        }

        return null;
    }

    private function ignoreMethod(Node\Stmt\ClassMethod $node): bool
    {
        return $node->isPrivate() || $node->isStatic() || $node->isFinal() || $node->isAbstract() || $node->isMagic();
    }

    private function resolvedParentMethodCall(Node\Stmt\ClassMethod $node): Node\Expr\MethodCall
    {
        return new Node\Expr\MethodCall($this->resolverCall(), $node->name->name);
    }

    private function resolverCall(): Node\Expr\PropertyFetch
    {
        return new Node\Expr\PropertyFetch(new Node\Expr\Variable('this'), $this->property);
    }
}