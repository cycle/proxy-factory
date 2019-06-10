<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Visitor;

use Cycle\ORM\Promise\Expressions;
use PhpParser\Builder;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

final class AddMagicCloneMethod extends NodeVisitorAbstract
{
    /** @var string */
    private $resolverProperty;
    private $hasClone;

    public function __construct(string $resolverProperty, bool $hasClone)
    {
        $this->resolverProperty = $resolverProperty;
        $this->hasClone = $hasClone;
    }

    public function leaveNode(Node $node)
    {
        if ($this->hasClone) {
            return null;
        }

        if ($node instanceof Node\Stmt\Class_) {
            $method = new Builder\Method('__clone');
            $method->makePublic();
            $method->addStmt($this->buildCloneExpression());

            $node->stmts[] = $method->getNode();
        }

        return null;
    }

    private function buildCloneExpression(): Node\Stmt\Expression
    {
        return new Node\Stmt\Expression(
            new Node\Expr\Assign(
                Expressions::resolvePropertyFetch('this', $this->resolverProperty),
                new Node\Expr\Clone_(Expressions::resolvePropertyFetch('this', $this->resolverProperty))
            )
        );
    }
}