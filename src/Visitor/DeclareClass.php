<?php
declare(strict_types=1);

namespace Spiral\Cycle\Promise\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * Declare proxy class, add extends and implements declarations
 */
class DeclareClass extends NodeVisitorAbstract
{
    /** @var string */
    private $name;

    /** @var string */
    private $extends;

    public function __construct(string $name, string $extends)
    {
        $this->name = $name;
        $this->extends = $extends;
    }

    /**
     * {@inheritdoc}
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Class_) {
            $node->extends = new Node\Name($this->extends);
            $node->name->name = $this->name;
        }

        return null;
    }
}