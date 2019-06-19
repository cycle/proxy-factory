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

final class AddMagicDebugInfoMethod extends NodeVisitorAbstract
{
    /** @var string */
    private $resolverProperty;

    /** @var string */
    private $resolveMethod;

    /** @var string */
    private $loadedMethod;

    /** @var string */
    private $roleMethod;

    /** @var string */
    private $scopeMethod;

    /** @var array */
    private $unsetPropertiesValues;

    /**
     * @param string $resolverProperty
     * @param string $resolveMethod
     * @param string $loadedMethod
     * @param string $roleMethod
     * @param string $scopeMethod
     * @param array  $unsetPropertiesValues
     */
    public function __construct(
        string $resolverProperty,
        string $resolveMethod,
        string $loadedMethod,
        string $roleMethod,
        string $scopeMethod,
        array $unsetPropertiesValues
    ) {
        $this->resolverProperty = $resolverProperty;
        $this->resolveMethod = $resolveMethod;
        $this->loadedMethod = $loadedMethod;
        $this->roleMethod = $roleMethod;
        $this->scopeMethod = $scopeMethod;
        $this->unsetPropertiesValues = $unsetPropertiesValues;
    }

    /**
     * @param Node $node
     * @return int|Node|Node[]|null
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Class_) {
            $method = new Builder\Method('__debugInfo');
            $method->makePublic();
            $method->addStmt($this->buildExpression());

            $node->stmts[] = $method->getNode();
        }

        return null;
    }

    /**
     * @return Node\Stmt\If_
     */
    private function buildExpression(): Node\Stmt\If_
    {
        $loaded = Expressions::resolveMethodCall('this', $this->resolverProperty, $this->loadedMethod);
        $if = new Node\Stmt\If_(Expressions::equalsFalse($loaded));
        $if->stmts[] = new Node\Stmt\Return_($this->unresolvedProperties('false'));
        $if->else = new Node\Stmt\Else_([
            Expressions::resolveIntoVar('entity', 'this', $this->resolverProperty, $this->resolveMethod),
            new Node\Stmt\If_(Expressions::notNull(new Node\Expr\Variable('entity')), [
                'stmts' => [new Node\Stmt\Return_($this->resolvedProperties())],
                'else'  => new Node\Stmt\Else_([
                    new Node\Stmt\Return_($this->unresolvedProperties('true'))
                ])
            ])
        ]);

        return $if;
    }

    /**
     * @return Node\Expr\Array_
     */
    private function resolvedProperties(): Node\Expr\Array_
    {
        $array = [];
        foreach ($this->unsetPropertiesValues as $value) {
            $array[] = $this->arrayItem(new Node\Expr\PropertyFetch(new Node\Expr\Variable('entity'), $value), $value);
        }

        return $this->array($array);
    }

    /**
     * @param string $loaded
     * @return Node\Expr\Array_
     */
    private function unresolvedProperties(string $loaded): Node\Expr\Array_
    {
        $array = [];
        $array[] = $this->arrayItem(Expressions::const($loaded), ':loaded');
        $array[] = $this->arrayItem(Expressions::const('false'), ':resolved');
        $array[] = $this->arrayItem(Expressions::resolveMethodCall('this', $this->resolverProperty, $this->roleMethod),
            ':role');
        $array[] = $this->arrayItem(Expressions::resolveMethodCall('this', $this->resolverProperty, $this->scopeMethod),
            ':scope');
        foreach ($this->unsetPropertiesValues as $value) {
            $array[] = $this->arrayItem(Expressions::const('null'), $value);
        }

        return $this->array($array);
    }

    /**
     * @param Node\Expr   $value
     * @param string|null $key
     * @return Node\Expr\ArrayItem
     */
    private function arrayItem(Node\Expr $value, string $key = null): Node\Expr\ArrayItem
    {
        return new Node\Expr\ArrayItem($value, new Node\Scalar\String_($key));
    }

    /**
     * @param array $array
     * @return Node\Expr\Array_
     */
    private function array(array $array): Node\Expr\Array_
    {
        return new Node\Expr\Array_($array, ['kind' => Node\Expr\Array_::KIND_SHORT]);
    }
}