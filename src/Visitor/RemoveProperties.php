<?php
declare(strict_types=1);

namespace Spiral\Cycle\Promise\Visitor;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use Spiral\Cycle\Promise\ProxyCreator;

/**
 * Remove use statements from the code.
 */
class RemoveProperties extends NodeVisitorAbstract
{
    /**
     * {@inheritdoc}
     */
    public function leaveNode(Node $node)
    {
        if (!$node instanceof Node\Stmt\Property) {
            return null;
        }

        return NodeTraverser::REMOVE_NODE;
    }
}