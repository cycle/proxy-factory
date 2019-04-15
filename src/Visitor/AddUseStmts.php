<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Visitor;

use Cycle\ORM\Promise\Utils;
use PhpParser\Builder\Use_;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * Add use statement to the code.
 */
class AddUseStmts extends NodeVisitorAbstract
{
    /** @var array */
    private $useStmts = [];

    public function __construct(array $useStmts)
    {
        $this->useStmts = $useStmts;
    }

    /**
     * {@inheritdoc}
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Namespace_) {
            $placementID = $this->definePlacementID($node);
            $node->stmts = Utils::injectValues($node->stmts, $placementID, $this->removeDuplicates($node->stmts, $this->buildUseStmts()));
        }

        return null;
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
     * @param Node\Stmt[] $stmts
     * @param Node[]      $nodes
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
                if (in_array($use->name->parts, $uses, true)) {
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
        foreach ($this->useStmts as $useStmt) {
            $stmts[] = $this->buildUseStmt($useStmt);
        }

        return $stmts;
    }

    /**
     * @param string $type
     *
     * @return Node
     */
    private function buildUseStmt(string $type): Node
    {
        $use_ = new Use_(new Node\Name($type), Node\Stmt\Use_::TYPE_NORMAL);

        return $use_->getNode();
    }
}