<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Visitor;

use Cycle\ORM\Promise\Expressions;
use PhpParser\Builder;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class AddMagicDebugInfo extends NodeVisitorAbstract
{
    /** @var string */
    private $resolverProperty;

    /** @var string */
    private $resolveMethod;

    /** @var array */
    private $unsetPropertiesValues;

    public function __construct(string $resolverProperty, string $resolveMethod, array $unsetPropertiesValues)
    {
        $this->resolverProperty = $resolverProperty;
        $this->resolveMethod = $resolveMethod;
        $this->unsetPropertiesValues = $unsetPropertiesValues;
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Class_) {
            $method = new Builder\Method('__debuginfo');
            $method->makePublic();
            $method->addStmt(Expressions::resolveIntoVar('entity', 'this', $this->resolverProperty, $this->resolveMethod));
            $method->addStmt($this->buildExpression());

            $node->stmts[] = $method->getNode();
        }

        return null;
    }

    private function buildExpression(): Node\Stmt\If_
    {
        $if = new Node\Stmt\If_(Expressions::notNull(new Node\Expr\Variable('entity')));
        $if->stmts[] = new Node\Stmt\Return_($this->resolvedProperties());
        $if->else = new Node\Stmt\Else_();
        $if->else->stmts[] = new Node\Stmt\Return_($this->unresolvedProperties());

        return $if;
    }

    private function resolvedProperties(): Node\Expr\Array_
    {
        $array = [];
        foreach ($this->unsetPropertiesValues as $value) {
            $array[] = $this->arrayItem(new Node\Expr\PropertyFetch(new Node\Expr\Variable('entity'), $value), $value);
        }

        return $this->array($array);
    }

    private function unresolvedProperties(): Node\Expr\Array_
    {
        $array = [];
        $array[] = $this->arrayItem(Expressions::const('true'), '~unresolved');
        foreach ($this->unsetPropertiesValues as $value) {
            $array[] = $this->arrayItem(Expressions::const('null'), $value);
        }

        return $this->array($array);
    }

    private function arrayItem(Node\Expr $value, string $key = null): Node\Expr\ArrayItem
    {
        return new Node\Expr\ArrayItem($value, new Node\Scalar\String_($key));
    }

    private function array(array $array): Node\Expr\Array_
    {
        return new Node\Expr\Array_($array, ['kind' => Node\Expr\Array_::KIND_SHORT]);
    }
}