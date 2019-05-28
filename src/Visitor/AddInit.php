<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Visitor;

use Cycle\ORM\Promise\Expressions;
use PhpParser\Builder;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * Add constructor
 */
class AddInit extends NodeVisitorAbstract
{
    /** @var string */
    private $property;

    /** @var string */
    private $type;

    /** @var array */
    private $dependencies;

    /** @var string */
    private $unsetPropertiesConst;

    /** @var string */
    private $initMethod;

    public function __construct(
        string $property,
        string $propertyType,
        array $dependencies,
        string $unsetPropertiesConst,
        string $initMethod
    ) {
        $this->property = $property;
        $this->type = $propertyType;
        $this->dependencies = $dependencies;
        $this->unsetPropertiesConst = $unsetPropertiesConst;
        $this->initMethod = $initMethod;
    }

    /**
     * {@inheritdoc}
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Class_) {
            $method = new Builder\Method($this->initMethod);
            $method->makePublic();
            $method->addParam((new Builder\Param('orm'))->setType('ORMInterface'));
            $method->addParam((new Builder\Param('role'))->setType('string'));
            $method->addParam((new Builder\Param('scope'))->setType('array'));
            $method->addStmt($this->unsetProperties());
            $method->addStmt($this->assignResolverProperty());

            $node->stmts[] = $method->getNode();
        }

        return null;
    }

    private function unsetProperties(): Node\Stmt\Foreach_
    {
        $prop = new Node\Expr\ClassConstFetch(new Node\Name('self'), $this->unsetPropertiesConst);
        $foreach = new Node\Stmt\Foreach_($prop, new Node\Expr\Variable('property'));
        $foreach->stmts[] = new Node\Stmt\Expression(Expressions::unsetFunc('this', '{$property}'));

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
}