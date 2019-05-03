<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Visitor;

use Cycle\ORM\Promise\Expressions;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class ModifyMagicIsset extends NodeVisitorAbstract
{
    /** @var string */
    private $resolverProperty;

    /** @var string */
    private $unsetPropertiesProperty;

    public function __construct(string $resolverProperty, string $unsetPropertiesProperty)
    {
        $this->unsetPropertiesProperty = $unsetPropertiesProperty;
        $this->resolverProperty = $resolverProperty;
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\ClassMethod && $node->name->name === '__isset') {
            $node->stmts[] = $this->buildInArrayExpression();
        }

        return null;

        if (in_array($name, $this->unsetProperties, true)) {
            $entity = $this->resolver->__resolve();
            unset($entity->{$name});
        } else {
            unset($this->{$name});
        }
    }

    private function buildInArrayExpression(): Node\Stmt\If_
    {
        $if = new Node\Stmt\If_(Expressions::inArrayFunc('name', 'this', $this->unsetPropertiesProperty));
        $if->stmts[] = Expressions::resolveIntoVar('entity', 'this', $this->resolverProperty, '__resolve');
        $if->stmts[] = new Node\Stmt\Return_(Expressions::issetFunc('entity', '{$name}'));
        $if->else = new Node\Stmt\Else_();
        $if->else->stmts[] = new Node\Stmt\Return_(Expressions::issetFunc('this', '{$name}'));

        return $if;
    }
}