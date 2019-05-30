<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Visitor;

use Cycle\ORM\Promise\Expressions;
use PhpParser\Builder;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class AddMagicSetMethod extends NodeVisitorAbstract
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

    private function buildSetExpression(): Node\Stmt\If_
    {
        $resolved = Expressions::resolveMethodCall('this', $this->resolverProperty, $this->resolveMethod);
        $stmt = new Node\Stmt\Expression(
            new Node\Expr\Assign(
                new Node\Expr\PropertyFetch($resolved, '{$name}'),
                new Node\Expr\Variable('value')
            )
        );

        return Expressions::throwExceptionOnNull($resolved, $stmt);
    }
}