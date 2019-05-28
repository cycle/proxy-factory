<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Visitor;

use Cycle\ORM\Promise\Expressions;
use PhpParser\Builder;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class AddMagicUnset extends NodeVisitorAbstract
{
    /** @var string */
    private $resolverProperty;

    /** @var string */
    private $resolveMethod;

    /** @var string */
    private $unsetPropertiesConst;

    public function __construct(string $resolverProperty, string $resolveMethod, string $unsetPropertiesConst)
    {
        $this->resolveMethod = $resolveMethod;
        $this->resolverProperty = $resolverProperty;
        $this->unsetPropertiesConst = $unsetPropertiesConst;
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Class_) {
            $method = new Builder\Method('__unset');
            $method->makePublic();
            $method->addParam(new Builder\Param('name'));
            $method->addStmt($this->buildUnsetExpression());

            $node->stmts[] = $method->getNode();
        }

        return null;
    }

    private function buildUnsetExpression(): Node\Stmt\If_
    {
        $if = new Node\Stmt\If_(Expressions::inConstArrayFunc('name', 'self', $this->unsetPropertiesConst));
        $if->stmts[] = Expressions::resolveIntoVar('entity', 'this', $this->resolverProperty, $this->resolveMethod);
        $if->stmts[] = Expressions::throwExceptionOnNull(
            new Node\Expr\Variable('entity'),
            Expressions::unsetFunc('entity', '{$name}')
        );
        $if->else = new Node\Stmt\Else_();
        $if->else->stmts[] = Expressions::unsetFunc('this', '{$name}');

        return $if;
    }
}