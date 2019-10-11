<?php

/**
 * Spiral Framework. Cycle ProxyFactory
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */
declare(strict_types=1);

namespace Cycle\ORM\Promise\Visitor;

use Cycle\ORM\Promise\PHPDoc;
use PhpParser\Builder;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

use function Cycle\ORM\Promise\resolveMethodCall;

final class AddPromiseMethod extends NodeVisitorAbstract
{
    /** @var string */
    private $resolverProperty;

    /** @var string */
    private $name;

    /** @var string|null */
    private $returnType;

    /**
     * @param string      $resolverProperty
     * @param string      $name
     * @param string|null $returnType
     */
    public function __construct(string $resolverProperty, string $name, string $returnType = null)
    {
        $this->resolverProperty = $resolverProperty;
        $this->name = $name;
        $this->returnType = $returnType;
    }

    /**
     * @param Node $node
     * @return int|Node|Node[]|null
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Class_) {
            $method = new Builder\Method($this->name);
            $method->makePublic();
            $method->addStmt(new Node\Stmt\Return_(resolveMethodCall(
                'this',
                $this->resolverProperty,
                $this->name
            )));
            if ($this->returnType !== null) {
                $method->setReturnType($this->returnType);
            }
            $method->setDocComment(PHPDoc::writeInheritdoc());

            $node->stmts[] = $method->getNode();
        }

        return null;
    }
}
