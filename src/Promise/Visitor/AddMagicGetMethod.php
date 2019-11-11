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

final class AddMagicGetMethod extends NodeVisitorAbstract
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
            $method = new Builder\Method('__get');
            $method->makePublic();
            $method->addParam(new Builder\Param('name'));
            $method->addStmt($this->buildGetExpression());

            $node->stmts[] = $method->getNode();
        }

        return null;
    }

    /**
     * @return Node\Stmt\If_
     */
    private function buildGetExpression(): Node\Stmt\If_
    {
        $resolved = resolveMethodCall('this', $this->resolverProperty, $this->resolveMethod);
        $stmt = new Node\Stmt\Return_(new Node\Expr\PropertyFetch($resolved, '{$name}'));

        return throwExceptionOnNull($resolved, $stmt);
    }
}
