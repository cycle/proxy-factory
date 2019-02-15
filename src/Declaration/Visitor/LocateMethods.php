<?php
declare(strict_types=1);

namespace Spiral\Cycle\Promise\Declaration\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class LocateMethods extends NodeVisitorAbstract
{
    /** @var string[] */
    private $methods = [];

    /** @var bool */
    private $hasConstructor = false;

    /**
     * {@inheritdoc}
     */
    public function leaveNode(Node $node)
    {
        if (!$node instanceof Node\Stmt\ClassMethod) {
            return null;
        }

        if ($node->name->name == '__construct') {
            $this->hasConstructor = true;
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

    public function hasConstructor(): bool
    {
        return $this->hasConstructor;
    }
}