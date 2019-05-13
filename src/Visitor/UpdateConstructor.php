<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Visitor;

use Cycle\ORM\Promise\Expressions;
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

    /** @var string */
    private $unsetPropertiesProperty;

    public function __construct(
        bool $hasConstructor,
        string $property,
        string $propertyType,
        array $dependencies,
        string $unsetPropertiesProperty
    ) {
        $this->hasConstructor = $hasConstructor;
        $this->property = $property;
        $this->type = $propertyType;
        $this->dependencies = $dependencies;
        $this->unsetPropertiesProperty = $unsetPropertiesProperty;
    }

    /**
     * {@inheritdoc}
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\ClassMethod && $node->name->name === '__construct') {
            $node->stmts[] = $this->unsetProperties();
            $node->stmts[] = $this->assignResolverProperty();

            if ($this->hasConstructor) {
                $node->stmts[] = $this->callParentConstruct();
            }
        }

        return null;
    }

    private function unsetProperties(): Node\Stmt\Foreach_
    {
        $prop = new Node\Expr\ClassConstFetch(new Node\Name('self'), $this->unsetPropertiesProperty);
        $foreach = new Node\Stmt\Foreach_($prop, new Node\Expr\Variable('property'));
        $foreach->stmts[] = Expressions::unsetFunc('this', '{$property}');

        return $foreach;
    }

    private function assignResolverProperty(): Node\Stmt\Expression
    {
        $prop = new Node\Expr\PropertyFetch(new Node\Expr\Variable('this'), $this->property);
        $instance = new Node\Expr\New_(new Node\Name($this->type), $this->packResolverPropertyArgs());

        return new Node\Stmt\Expression(new Node\Expr\Assign($prop, $instance));
    }

    private function packResolverPropertyArgs(): array
    {
        $args = [];
        foreach ($this->dependencies as $name => $type) {
            $args[] = new Node\Arg(new Node\Expr\Variable($name));
        }

        return $args;
    }

    private function callParentConstruct(): Node\Stmt\Expression
    {
        return new Node\Stmt\Expression(new Node\Expr\StaticCall(new Node\Name('parent'), '__construct'));
    }
}