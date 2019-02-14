<?php
declare(strict_types=1);

namespace Spiral\Cycle\Promise\Visitor;

use PhpParser\Builder\Use_;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Spiral\Cycle\Promise\ProxyCreator;
use Spiral\Cycle\Promise\Utils;

/**
 * Add use statement to the code.
 */
class AddUseStmts extends NodeVisitorAbstract
{
    /**
     * {@inheritdoc}
     */
    public function leaveNode(Node $node)
    {
        if (!$node instanceof Node\Stmt\Namespace_) {
            return null;
        }

        $placementID = $this->definePlacementID($node);
        $node->stmts = Utils::injectValues($node->stmts, $placementID, $this->removeDuplicates($node->stmts, $this->buildUseStmts()));

        return $node;
    }

    private function definePlacementID(Node\Stmt\Namespace_ $node): int
    {
        foreach ($node->stmts as $index => $child) {
            if ($child instanceof Node\Stmt\Class_) {
                return $index;
            }
        }

        return 0;
    }

    /**
     * @param Node\Stmt[]      $stmts
     * @param Node\Stmt\Use_[] $nodes
     *
     * @return Node\Stmt\Use_[]
     */
    private function removeDuplicates(array $stmts, array $nodes): array
    {
        $uses = $this->getExistingUseParts($stmts);

        foreach ($nodes as $i => $node) {
            if (!$node instanceof Node\Stmt\Use_) {
                continue;
            }

            foreach ($node->uses as $use) {
                if (in_array($use->name->parts, $uses)) {
                    unset($nodes[$i]);
                }
            }
        }

        return $nodes;
    }

    /**
     * @param Node\Stmt[] $stmts
     *
     * @return string[]
     */
    private function getExistingUseParts(array $stmts): array
    {
        $parts = [];
        foreach ($stmts as $stmt) {
            if (!$stmt instanceof Node\Stmt\Use_) {
                continue;
            }

            foreach ($stmt->uses as $use) {
                $parts[] = $use->name->parts;
            }
        }

        return $parts;
    }

    /**
     * @return Node[]
     */
    private function buildUseStmts(): array
    {
        $stmts = [];
        foreach (ProxyCreator::PROXY_DEPENDENCIES as $dependency) {
            $stmts[] = $this->buildUse($dependency);
        }

        return $stmts;
    }

    /**
     * @param string $type
     *
     * @return Node
     */
    private function buildUse(string $type): Node
    {
        $use_ = new Use_(new Node\Name($type), Node\Stmt\Use_::TYPE_NORMAL);

        return $use_->getNode();
    }
}