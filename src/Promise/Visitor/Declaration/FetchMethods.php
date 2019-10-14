<?php

/**
 * Spiral Framework. Cycle ProxyFactory
 *
 * @license MIT
 * @author  Valentin V (vvval)
 */
declare(strict_types=1);

namespace Cycle\ORM\Promise\Visitor\Declaration;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class FetchMethods extends NodeVisitorAbstract
{
    /** @var Node\Stmt\ClassMethod[] */
    private $methods = [];

    /**
     * {@inheritDoc}
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\ClassMethod) {
            $this->methods[(string)$node->name] = $node;
        }

        return null;
    }

    /**
     * @return Node\Stmt\ClassMethod[]
     */
    public function getMethods(): array
    {
        return $this->methods;
    }
}
