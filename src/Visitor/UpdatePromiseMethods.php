<?php
declare(strict_types=1);

namespace Spiral\Cycle\Promise\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Spiral\Cycle\Promise\PHPDoc;

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
            $node->setDocComment(PHPDoc::writeInheritdoc());
            $node->stmts = [new Node\Stmt\Return_($this->resolvedParentMethodCall($node))];
        }

        return null;
    }

    private function ignoreMethod(Node\Stmt\ClassMethod $node): bool
    {
        if ($node->isPrivate() || $node->isStatic() || $node->isFinal() || $node->isAbstract() || $node->isMagic()) {
            return true;
        }

        return false;
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