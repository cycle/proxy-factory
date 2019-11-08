<?php

/**
 * Spiral Framework. Cycle ProxyFactory
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */

declare(strict_types=1);

namespace Cycle\ORM\Promise\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

use function Cycle\ORM\Promise\resolvePropertyFetch;

/**
 * Modify all accessible methods
 */
final class UpdatePromiseMethods extends NodeVisitorAbstract
{
    /** @var string */
    private $property;

    /**
     * @param string $property
     */
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

    /**
     * @param Node\Stmt\ClassMethod $node
     * @return bool
     */
    private function ignoreMethod(Node\Stmt\ClassMethod $node): bool
    {
        return $node->isPrivate() || $node->isStatic() || $node->isFinal() || $node->isAbstract() || $node->isMagic();
    }

    /**
     * @param Node\Stmt\ClassMethod $node
     * @return Node\Expr\MethodCall
     */
    private function resolvedParentMethodCall(Node\Stmt\ClassMethod $node): Node\Expr\MethodCall
    {
        return new Node\Expr\MethodCall(resolvePropertyFetch('this', $this->property), $node->name->name);
    }
}
