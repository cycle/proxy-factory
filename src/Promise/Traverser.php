<?php

/**
 * Spiral Framework. Cycle ProxyFactory
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */
declare(strict_types=1);

namespace Cycle\ORM\Promise;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;

final class Traverser
{
    /**
     * @param Node[]      $nodes
     * @param NodeVisitor ...$visitors
     * @return Node[]
     */
    public function traverseClonedNodes(array $nodes, NodeVisitor ...$visitors): array
    {
        return $this->makeTraverser(...$visitors)->traverse($this->cloneNodes($nodes));
    }

    /**
     * @param array       $nodes
     * @param NodeVisitor ...$visitors
     */
    public function traverse(array $nodes, NodeVisitor ...$visitors): void
    {
        $this->makeTraverser(...$visitors)->traverse($nodes);
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    private function cloneNodes(array $nodes): array
    {
        return $this->makeTraverser(new NodeVisitor\CloningVisitor())->traverse($nodes);
    }

    /**
     * @param NodeVisitor ...$visitors
     * @return NodeTraverser
     */
    private function makeTraverser(NodeVisitor ...$visitors): NodeTraverser
    {
        $traverser = new NodeTraverser();
        foreach ($visitors as $visitor) {
            $traverser->addVisitor($visitor);
        }

        return $traverser;
    }
}
