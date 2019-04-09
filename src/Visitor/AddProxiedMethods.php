<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Visitor;

use Cycle\ORM\Promise\PHPDoc;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * Add parent call via proxy resolver
 */
class AddProxiedMethods extends NodeVisitorAbstract
{
    /** @var string */
    private $property;

    /** @var Node\Stmt\ClassMethod[] */
    private $methods;

    /** @var string */
    private $resolveMethod;

    public function __construct(string $property, array $methods, string $resolveMethod)
    {
        $this->property = $property;
        $this->methods = $methods;
        $this->resolveMethod = $resolveMethod;
    }

    /**
     * {@inheritdoc}
     */
    public function leaveNode(Node $node)
    {
        if (!$node instanceof Node\Stmt\Class_) {
            return null;
        }

        foreach ($this->methods as $method) {
            $node->stmts[] = $this->modifyMethod($method);
        }

        return $node;
    }

    private function modifyMethod(Node\Stmt\ClassMethod $method): Node\Stmt\ClassMethod
    {
        $method->setDocComment(PHPDoc::writeInheritdoc());
        $method->stmts = [
            new Node\Stmt\Return_(
                new Node\Expr\MethodCall(
                    new Node\Expr\MethodCall(
                        new Node\Expr\PropertyFetch(new Node\Expr\Variable('this'), $this->property),
                        $this->resolveMethod
                    ),
                    $method->name->name
                )
            )
        ];

        return $method;
    }
}