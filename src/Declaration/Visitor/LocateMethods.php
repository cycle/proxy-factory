<?php
declare(strict_types=1);

namespace Spiral\Cycle\Promise\Declaration\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class LocateMethods extends NodeVisitorAbstract
{
    /** @var string[] */
    private $methods = [];

    /**
     * {@inheritdoc}
     */
    public function leaveNode(Node $node)
    {
        if (!$node instanceof Node\Stmt\ClassMethod) {
            return null;
        }

        if ($node->isPrivate() || $node->isStatic() || $node->isFinal() || $node->isAbstract() || $node->isMagic()) {
            return null;
        }

        $this->methods[] = $node->name->name;

        return null;
    }

    public function getMethods(): array
    {
        return $this->methods;
    }
}