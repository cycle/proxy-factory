<?php
declare(strict_types=1);

namespace Spiral\Cycle\Promise\Visitor;

use PhpParser\Builder\Property;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Spiral\Cycle\Promise\Utils;

/**
 * Add resolver property
 */
class AddProperty extends NodeVisitorAbstract
{
    /** @var string */
    private $name;

    /** @var string */
    private $type;

    public function __construct(string $name, string $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function leaveNode(Node $node)
    {
        if (!$node instanceof Node\Stmt\Class_) {
            return null;
        }

        $node->stmts = Utils::injectValues($node->stmts, $this->definePlacementID($node), [$this->buildProperty()]);

        return $node;
    }

    private function definePlacementID(Node\Stmt\Class_ $node): int
    {
        foreach ($node->stmts as $index => $child) {
            if ($child instanceof Node\Stmt\ClassMethod || $child instanceof Node\Stmt\Property) {
                return $index;
            }
        }

        return 0;
    }

    private function buildProperty(): Node\Stmt\Property
    {
        $property = new Property($this->name);
        $property->makeProtected();
        $property->setDocComment(new Doc("/** @var {$this->type} */"));

        return $property->getNode();
    }
}