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
use Cycle\ORM\Promise\StatementsInjector;
use PhpParser\Builder\Property;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * Add resolver property
 */
final class AddResolverProperty extends NodeVisitorAbstract
{
    /** @var string */
    private $property;

    /** @var string */
    private $type;

    /** @var string */
    private $class;

    /** @var StatementsInjector */
    private $injector;

    /**
     * @param string $property
     * @param string $type
     * @param string $class
     */
    public function __construct(string $property, string $type, string $class)
    {
        $this->property = $property;
        $this->type = $type;
        $this->class = $class;
        $this->injector = new StatementsInjector();
    }

    /**
     * {@inheritdoc}
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Class_) {
            $node->stmts = $this->injector->inject(
                $node->stmts,
                Node\Stmt\ClassMethod::class,
                [$this->buildProperty()]
            );
        }

        return null;
    }

    /**
     * @return Node\Stmt\Property
     */
    private function buildProperty(): Node\Stmt\Property
    {
        $property = new Property($this->property);
        $property->makePrivate();
        $property->setDocComment(PHPDoc::writeProperty("{$this->type}|{$this->class}"));

        return $property->getNode();
    }
}
