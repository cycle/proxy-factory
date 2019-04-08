<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;

final class Traverser
{
    /**
     * @param Node\Stmt[] $nodes
     * @param NodeVisitor ...$visitors
     *
     * @return Node[]
     */
    public function traverseClonedNodes(array $nodes, NodeVisitor ...$visitors): array
    {
        return $this->makeTraverser(...$visitors)->traverse($this->cloneNodes($nodes));
    }

    /**
     * @param Node[] $nodes
     *
     * @return Node[]
     */
    private function cloneNodes(array $nodes): array
    {
        return $this->makeTraverser(new NodeVisitor\CloningVisitor())->traverse($nodes);
    }

    private function makeTraverser(NodeVisitor ...$visitors): NodeTraverser
    {
        $traverser = new NodeTraverser();
        foreach ($visitors as $visitor) {
            $traverser->addVisitor($visitor);
        }

        return $traverser;
    }
}