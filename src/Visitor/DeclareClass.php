<?php
declare(strict_types=1);

namespace Spiral\Cycle\Promise\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Spiral\Cycle\Promise\PromiseInterface;
use Spiral\Cycle\Promise\Utils;

/**
 * Declare proxy class, add extends and implements declarations
 */
class DeclareClass extends NodeVisitorAbstract
{
    /**
     * {@inheritdoc}
     */
    public function leaveNode(Node $node)
    {
        if (!$node instanceof Node\Stmt\Class_) {
            return null;
        }

        $node->extends = new Node\Stmt\Class_($node->name->name);
        $node->implements = [new Node\Stmt\Interface_(Utils::shortName(PromiseInterface::class))];
        $node->name->name .= 'Proxy';

        return $node;
    }
}