<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Visitor;

use Cycle\ORM\Promise\Expressions;
use PhpParser\Builder;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class AddMagicIssetMethod extends NodeVisitorAbstract
{
    /** @var string */
    private $resolverProperty;

    /** @var string */
    private $resolveMethod;

    /** @var string */
    private $unsetPropertiesProperty;

    public function __construct(string $resolverProperty, string $resolveMethod, string $unsetPropertiesProperty)
    {
        $this->unsetPropertiesProperty = $unsetPropertiesProperty;
        $this->resolveMethod = $resolveMethod;
        $this->resolverProperty = $resolverProperty;
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Class_) {
            $method = new Builder\Method('__isset');
            $method->makePublic();
            $method->addParam(new Builder\Param('name'));
            $method->addStmt($this->buildIssetExpression());

            $node->stmts[] = $method->getNode();
        }

        return null;
    }

    private function buildIssetExpression(): Node\Stmt\If_
    {
        $if = new Node\Stmt\If_(Expressions::inConstArrayFunc('name', 'self', $this->unsetPropertiesProperty));
        $if->stmts[] = Expressions::resolveIntoVar('entity', 'this', $this->resolverProperty, $this->resolveMethod);
        $if->stmts[] = Expressions::throwExceptionOnNull(
            new Node\Expr\Variable('entity'),
            new Node\Stmt\Return_(Expressions::issetFunc('entity', '{$name}'))
        );
        $if->else = new Node\Stmt\Else_();
        $if->else->stmts[] = new Node\Stmt\Return_(Expressions::issetFunc('this', '{$name}'));

        return $if;
    }
}