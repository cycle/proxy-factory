<?php
/**
 * Spiral Framework.
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */
declare(strict_types=1);

namespace Cycle\ORM\Promise\Visitor;

use Cycle\ORM\Promise\PHPDoc;
use Cycle\ORM\Promise\Utils;
use PhpParser\Builder\Property;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * Add resolver property
 */
final class AddResolverProperty extends NodeVisitorAbstract
{
    /** @var string */
    private $property;

    /** @var string */
    private $type;

    /** @var string|null */
    private $parent;

    public function __construct(string $property, string $type, ?string $parent)
    {
        $this->property = $property;
        $this->type = $type;
        $this->parent = $parent;
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
        $property = new Property($this->property);
        $property->makePrivate();

        $type = $this->type;
        if ($this->parent !== null) {
            $type .= "|{$this->parent}";
        }
        $property->setDocComment(PHPDoc::writeProperty($type));

        return $property->getNode();
    }
}