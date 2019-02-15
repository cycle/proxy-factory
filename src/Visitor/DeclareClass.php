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
    /** @var string */
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function leaveNode(Node $node)
    {
        if (!$node instanceof Node\Stmt\Class_) {
            return null;
        }

        if ($node->isAbstract()) {
            $this->removeAbstract($node);
        }

        $node->extends = new Node\Name($node->name->name);
        $node->implements = [new Node\Name(Utils::shortName(PromiseInterface::class))];
        $node->name->name = $this->name;

        return $node;
    }

    private function removeAbstract(Node\Stmt\Class_ $node)
    {
        $node->flags = $node->flags ^ Node\Stmt\Class_::MODIFIER_ABSTRACT;
    }
}