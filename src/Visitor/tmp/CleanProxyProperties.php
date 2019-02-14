<?php
declare(strict_types=1);

namespace Spiral\Cycle\Promise\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

class CleanProxyProperties extends NodeVisitorAbstract
{
    public function leaveNode(Node $node)
    {
        if (!$node instanceof Node\Stmt\Property) {
            return null;
        }

        return NodeTraverser::REMOVE_NODE;
    }
}