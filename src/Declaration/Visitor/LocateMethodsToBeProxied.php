<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Declaration\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class LocateMethodsToBeProxied extends NodeVisitorAbstract
{
    /** @var string[] */
    private $methods = [];

    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * {@inheritdoc}
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\ClassMethod && !$this->isIgnoredMethod($node)) {
            $this->methods[] = $node;
        }

        return null;
    }

    private function isIgnoredMethod(Node\Stmt\ClassMethod $node): bool
    {
        if ($node->isPrivate() || $node->isStatic() || $node->isFinal() || $node->isAbstract() || $node->isMagic()) {
            return true;
        }

        return false;
    }
}