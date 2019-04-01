<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * Add constructor
 */
class UpdateConstructor extends NodeVisitorAbstract
{
    /** @var bool */
    private $hasConstructor;

    /** @var string */
    private $property;

    /** @var string */
    private $type;

    /** @var array */
    private $dependencies;

    public function __construct(bool $hasConstructor, string $property, string $propertyType, array $dependencies)
    {
        $this->hasConstructor = $hasConstructor;
        $this->property = $property;
        $this->type = $propertyType;
        $this->dependencies = $dependencies;
    }

    /**
     * {@inheritdoc}
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\ClassMethod && $node->name->name === '__construct') {
            $node->stmts[] = $this->makeResolverPropertyAssignment();
            if ($this->hasConstructor) {
                $node->stmts[] = $this->makeParentConstructorCall();
            }
        }

        return null;
    }

    private function makeResolverPropertyAssignment(): Node\Stmt\Expression
    {
        $prop = new Node\Expr\PropertyFetch(new Node\Expr\Variable('this'), $this->property);
        $instance = new Node\Expr\New_(new Node\Name($this->type), $this->makeResolverPropertyInstantiationArgs());

        return new Node\Stmt\Expression(new Node\Expr\Assign($prop, $instance));
    }

    private function makeResolverPropertyInstantiationArgs(): array
    {
        $args = [];
        foreach ($this->dependencies as $name => $type) {
            $args[] = new Node\Arg(new Node\Expr\Variable($name));
        }

        return $args;
    }

    private function makeParentConstructorCall(): Node\Stmt\Expression
    {
        return new Node\Stmt\Expression(new Node\Expr\StaticCall(new Node\Name('parent'), '__construct'));
    }
}