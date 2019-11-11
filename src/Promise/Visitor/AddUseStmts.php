<?php

/**
 * Spiral Framework. Cycle ProxyFactory
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */

declare(strict_types=1);

namespace Cycle\ORM\Promise\Visitor;

use Cycle\ORM\Promise\StatementsInjector;
use PhpParser\Builder\Use_;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * Add use statement to the code.
 */
final class AddUseStmts extends NodeVisitorAbstract
{
    /** @var array */
    private $useStmts = [];

    /** @var StatementsInjector */
    private $injector;

    /**
     * @param array $useStmts
     */
    public function __construct(array $useStmts)
    {
        $this->useStmts = $useStmts;
        $this->injector = new StatementsInjector();
    }

    /**
     * {@inheritdoc}
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Namespace_) {
            $node->stmts = $this->injector->inject(
                $node->stmts,
                Node\Stmt\Class_::class,
                $this->removeDuplicates($node->stmts, $this->buildUseStmts())
            );
        }

        return null;
    }

    /**
     * @param Node\Stmt[] $stmts
     * @param Node[]      $nodes
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
        foreach ($this->useStmts as $name) {
            $stmt = new Use_(new Node\Name($name), Node\Stmt\Use_::TYPE_NORMAL);
            $stmts[] = $stmt->getNode();
        }

        return $stmts;
    }
}
