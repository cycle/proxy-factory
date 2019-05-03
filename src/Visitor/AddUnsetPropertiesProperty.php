<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Visitor;

use Cycle\ORM\Promise\PHPDoc;
use Cycle\ORM\Promise\Utils;
use PhpParser\Builder\Property;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * Add "unset properties" property
 */
class AddUnsetPropertiesProperty extends NodeVisitorAbstract
{
    /** @var string */
    private $unsetProperty;

    /** @var array */
    private $unsetPropertiesValues;

    public function __construct(string $unsetPropertiesProperty, array $unsetPropertiesValues)
    {
        $this->unsetProperty = $unsetPropertiesProperty;
        $this->unsetPropertiesValues = $unsetPropertiesValues;
    }

    /**
     * {@inheritdoc}
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Class_) {
            $node->stmts = Utils::injectValues($node->stmts, $this->definePlacementID($node), [$this->buildProperty()]);
        }

        return null;
    }

    private function definePlacementID(Node\Stmt\Class_ $node): int
    {
        foreach ($node->stmts as $index => $child) {
            if ($child instanceof Node\Stmt\ClassMethod) {
                return $index;
            }
        }

        return 0;
    }

    private function buildProperty(): Node\Stmt\Property
    {
        $property = new Property($this->unsetProperty);
        $property->makePrivate();
        $property->setDocComment(PHPDoc::writeProperty('array'));
        $property->setDefault($this->unsetPropertiesValues);

        return $property->getNode();
    }
}