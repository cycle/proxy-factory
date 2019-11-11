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
use PhpParser\Builder\Property;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

use function Cycle\ORM\Promise\inject;

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
    }

    /**
     * {@inheritdoc}
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Class_) {
            $node->stmts = inject(
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
