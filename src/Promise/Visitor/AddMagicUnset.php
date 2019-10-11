<?php

/**
 * Spiral Framework. Cycle ProxyFactory
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */
declare(strict_types=1);

namespace Cycle\ORM\Promise\Visitor;

use PhpParser\Builder;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

use function Cycle\ORM\Promise\inConstArrayFunc;
use function Cycle\ORM\Promise\resolveIntoVar;
use function Cycle\ORM\Promise\throwExceptionOnNull;
use function Cycle\ORM\Promise\unsetFuncExpr;

final class AddMagicUnset extends NodeVisitorAbstract
{
    /** @var string */
    private $resolverProperty;

    /** @var string */
    private $resolveMethod;

    /** @var string */
    private $unsetPropertiesConst;

    /**
     * @param string $resolverProperty
     * @param string $resolveMethod
     * @param string $unsetPropertiesConst
     */
    public function __construct(string $resolverProperty, string $resolveMethod, string $unsetPropertiesConst)
    {
        $this->resolveMethod = $resolveMethod;
        $this->resolverProperty = $resolverProperty;
        $this->unsetPropertiesConst = $unsetPropertiesConst;
    }

    /**
     * @param Node $node
     * @return int|Node|Node[]|null
     */
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

    /**
     * @return Node\Stmt\If_
     */
    private function buildUnsetExpression(): Node\Stmt\If_
    {
        $if = new Node\Stmt\If_(inConstArrayFunc('name', 'self', $this->unsetPropertiesConst));
        $if->stmts[] = resolveIntoVar('entity', 'this', $this->resolverProperty, $this->resolveMethod);
        $if->stmts[] = throwExceptionOnNull(
            new Node\Expr\Variable('entity'),
            new Node\Stmt\Expression(unsetFuncExpr('entity', '{$name}'))
        );
        $if->else = new Node\Stmt\Else_([
            new Node\Stmt\Expression(unsetFuncExpr('this', '{$name}'))
        ]);

        return $if;
    }
}
