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
class RemoveUseStmts extends NodeVisitorAbstract
{
    /**
     * {@inheritdoc}
     */
    public function leaveNode(Node $node)
    {
        if (!$node instanceof Node\Stmt\Use_) {
            return null;
        }

        foreach ($node->uses as $index => $use) {
            if (!$this->isReserved($use)) {
                unset($node->uses[$index]);
            }
        }

        if (empty($node->uses)) {
            return NodeTraverser::REMOVE_NODE;
        }

        return $node;
    }

    private function isReserved(Node\Stmt\UseUse $use): bool
    {
        return in_array($use->name->toString(), ProxyCreator::PROXY_DEPENDENCIES);
    }
}