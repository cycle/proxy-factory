<?php

/**
 * Spiral Framework. Cycle ProxyFactory
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */

declare(strict_types=1);

namespace Cycle\ORM\Promise\Visitor;

use Cycle\ORM\Promise\StatementsInjector;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * Add "unset properties" property
 */
final class AddUnsetPropertiesConst extends NodeVisitorAbstract
{
    /** @var string */
    private $unsetPropertyConst;

    /** @var array */
    private $unsetPropertiesValues;

    /** @var StatementsInjector */
    private $injector;

    /**
     * @param string $unsetPropertiesConst
     * @param array  $unsetPropertiesValues
     */
    public function __construct(string $unsetPropertiesConst, array $unsetPropertiesValues)
    {
        $this->unsetPropertyConst = $unsetPropertiesConst;
        $this->unsetPropertiesValues = $unsetPropertiesValues;
        $this->injector = new StatementsInjector();
    }

    /**
     * {@inheritdoc}
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Class_) {
            $node->stmts = $this->injector->inject(
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
        foreach ($this->unsetPropertiesValues as $value) {
            $array[] = new Node\Expr\ArrayItem(new Node\Scalar\String_($value));
        }

        $const = new Node\Stmt\ClassConst([
            new Node\Const_(
                $this->unsetPropertyConst,
                new Node\Expr\Array_($array, ['kind' => Node\Expr\Array_::KIND_SHORT])
            )
        ], Node\Stmt\Class_::MODIFIER_PRIVATE);

        return $const;
    }
}
