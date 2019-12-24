<?php

/**
 * Spiral Framework. Cycle ProxyFactory
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */

declare(strict_types=1);

namespace Cycle\ORM\Promise\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

use function Cycle\ORM\Promise\inject;

/**
 * Add const with properties list
 */
final class AddPropertiesConst extends NodeVisitorAbstract
{
    /** @var string */
    private $name;

    /** @var array */
    private $values;

    /**
     * @param string $name
     * @param array  $values
     */
    public function __construct(string $name, array $values)
    {
        $this->name = $name;
        $this->values = $values;
    }

    /**
     * {@inheritdoc}
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Class_) {
            $node->stmts = inject(
                $node->stmts,
                Node\Stmt\ClassMethod::class,
                [$this->buildProperty()]
            );
        }

        return null;
    }

    /**
     * @return Node\Stmt\ClassConst
     */
    private function buildProperty(): Node\Stmt\ClassConst
    {
        $array = [];
        foreach ($this->values as $value) {
            $array[] = new Node\Expr\ArrayItem(new Node\Scalar\String_($value));
        }

        return new Node\Stmt\ClassConst([
            new Node\Const_(
                $this->name,
                new Node\Expr\Array_($array, ['kind' => Node\Expr\Array_::KIND_SHORT])
            )
        ], Node\Stmt\Class_::MODIFIER_PRIVATE);
    }
}
