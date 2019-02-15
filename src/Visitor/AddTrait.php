<?php
declare(strict_types=1);

namespace Spiral\Cycle\Promise\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Spiral\Cycle\Promise\ResolverTrait;
use Spiral\Cycle\Promise\Utils;

/**
 * Add resolver trait
 */
class AddTrait extends NodeVisitorAbstract
{
    /**
     * {@inheritdoc}
     */
    public function leaveNode(Node $node)
    {
        if (!$node instanceof Node\Stmt\Class_) {
            return null;
        }

        if (!$this->hasAnyTraits($node)) {
            $this->createTraits($node);
        } elseif (!$this->hasTrait($node)) {
            $this->addTrait($node);
        }

        return $node;
    }

    private function hasAnyTraits(Node\Stmt\Class_ $node): bool
    {
        foreach ($node->stmts as $stmt) {
            if ($stmt instanceof Node\Stmt\TraitUse) {
                return true;
            }
        }

        return false;
    }

    private function createTraits(Node\Stmt\Class_ $node)
    {
        array_unshift($node->stmts, new Node\Stmt\TraitUse([
            new Node\Stmt\Trait_($this->traitName())
        ]));
    }

    private function hasTrait(Node\Stmt\Class_ $node): bool
    {
        $traits = $this->getTraitsArray($node);

        return in_array($this->traitName(), $traits);
    }

    private function getTraitsArray(Node\Stmt\Class_ $node): array
    {
        $traits = [];
        foreach ($node->stmts as $stmt) {
            if (!$stmt instanceof Node\Stmt\TraitUse) {
                continue;
            }

            foreach ($stmt->traits as $trait) {
                $traits[] = $trait->parts[0];
            }
        }

        return $traits;
    }

    private function addTrait(Node\Stmt\Class_ $node)
    {
        foreach ($node->stmts as $stmt) {
            if (!$stmt instanceof Node\Stmt\TraitUse) {
                continue;
            }

            $stmt->traits[] = new Node\Stmt\Trait_($this->traitName());
        }
    }

    private function traitName(): string
    {
        return Utils::shortName(ResolverTrait::class);
    }
}