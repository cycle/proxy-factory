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

use function Cycle\ORM\Promise\resolveMethodCall;
use function Cycle\ORM\Promise\throwExceptionOnNull;

final class AddMagicSetMethod extends NodeVisitorAbstract
{
    /** @var string */
    private $resolverProperty;

    /** @var string */
    private $resolveMethod;

    /**
     * @param string $resolverProperty
     * @param string $resolveMethod
     */
    public function __construct(string $resolverProperty, string $resolveMethod)
    {
        $this->resolverProperty = $resolverProperty;
        $this->resolveMethod = $resolveMethod;
    }

    /**
     * @param Node $node
     * @return int|Node|Node[]|null
     */
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

    /**
     * @return Node\Stmt\If_
     */
    private function buildSetExpression(): Node\Stmt\If_
    {
        $resolved = resolveMethodCall('this', $this->resolverProperty, $this->resolveMethod);
        $stmt = new Node\Stmt\Expression(
            new Node\Expr\Assign(
                new Node\Expr\PropertyFetch($resolved, '{$name}'),
                new Node\Expr\Variable('value')
            )
        );

        return throwExceptionOnNull($resolved, $stmt);
    }
}
