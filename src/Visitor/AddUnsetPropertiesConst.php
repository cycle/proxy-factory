<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Visitor;

use Cycle\ORM\Promise\Utils;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * Add "unset properties" property
 */
class AddUnsetPropertiesConst extends NodeVisitorAbstract
{
    /** @var string */
    private $unsetPropertyConst;

    /** @var array */
    private $unsetPropertiesValues;

    public function __construct(string $unsetPropertiesConst, array $unsetPropertiesValues)
    {
        $this->unsetPropertyConst = $unsetPropertiesConst;
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

    private function buildProperty(): Node\Stmt\ClassConst
    {
        $array = [];
        foreach ($this->unsetPropertiesValues as $value) {
            $array[] = new Node\Expr\ArrayItem(new Node\Scalar\String_($value));
        }

        $const = new Node\Stmt\ClassConst([
            new Node\Const_($this->unsetPropertyConst, new Node\Expr\Array_($array, ['kind' => Node\Expr\Array_::KIND_SHORT]))
        ], Node\Stmt\Class_::MODIFIER_PRIVATE);

        return $const;
    }
}