<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Visitor;

use Cycle\ORM\Promise\Expressions;
use PhpParser\Builder;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class AddMagicGet extends NodeVisitorAbstract
{
    /** @var string */
    private $resolverProperty;

    /** @var string */
    private $resolveMethod;

    public function __construct(string $resolverProperty, string $resolveMethod)
    {
        $this->resolverProperty = $resolverProperty;
        $this->resolveMethod = $resolveMethod;
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Class_) {
            $method = new Builder\Method('__get');
            $method->makePublic();
            $method->addParam(new Builder\Param('name'));
            $method->addStmt($this->buildGetExpression());

            $node->stmts[] = $method->getNode();
        }

        return null;
    }

    private function buildGetExpression(): Node\Stmt\Return_
    {
        return new Node\Stmt\Return_(
            new Node\Expr\PropertyFetch(Expressions::resolveMethodCall('this', $this->resolverProperty, $this->resolveMethod), '{$name}')
        );
    }
}