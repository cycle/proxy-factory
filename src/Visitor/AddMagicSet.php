<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Visitor;

use Cycle\ORM\Promise\Expressions;
use PhpParser\Builder;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class AddMagicSet extends NodeVisitorAbstract
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
            $method = new Builder\Method('__set');
            $method->makePublic();
            $method->addParams([new Builder\Param('name'), new Builder\Param('value')]);
            $method->addStmt($this->buildSetExpression());

            $node->stmts[] = $method->getNode();
        }

        return null;
    }

    private function buildSetExpression(): Node\Stmt\Expression
    {
        return new Node\Stmt\Expression(
            new Node\Expr\Assign(
                new Node\Expr\PropertyFetch(Expressions::resolveMethodCall('this', $this->resolverProperty, $this->resolveMethod), '{$name}'),
                new Node\Expr\Variable('value')
            )
        );
    }
}