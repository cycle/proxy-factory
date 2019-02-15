<?php
declare(strict_types=1);

namespace Spiral\Cycle\Promise\Declaration\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class LocateProperties extends NodeVisitorAbstract
{
    /** @var string[] */
    private $properties = [];

    /**
     * {@inheritdoc}
     */
    public function leaveNode(Node $node)
    {
        if (!$node instanceof Node\Stmt\Property) {
            return null;
        }

        if ($node->isPrivate() || $node->isStatic()) {
            return null;
        }

        foreach ($node->props as $prop) {
            $this->properties[] = $prop->name->name;
        }

        return null;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }
}