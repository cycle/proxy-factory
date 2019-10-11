<?php

/**
 * Spiral Framework.
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */
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

    /** @var bool */
    private $hasClone;

    /**
     * @param string $resolverProperty
     * @param bool   $hasClone
     */
    public function __construct(string $resolverProperty, bool $hasClone)
    {
        $this->resolverProperty = $resolverProperty;
        $this->hasClone = $hasClone;
    }

    /**
     * @param Node $node
     * @return int|Node|Node[]|null
     */
    public function leaveNode(Node $node)
    {
        if ($this->hasClone) {
            return null;
        }

        if ($node instanceof Node\Stmt\Class_) {
            $method = new Builder\Method('__clone');
            $method->makePublic();
            $method->addStmt(Expressions::buildCloneExpression($this->resolverProperty));

            $node->stmts[] = $method->getNode();
        }

        return null;
    }
}
