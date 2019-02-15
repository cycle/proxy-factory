<?php
declare(strict_types=1);

namespace Spiral\Cycle\Promise\Visitor;

use PhpParser\Builder\Method;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * Add resolver method (getter)
 */
class AddResolverGetter extends NodeVisitorAbstract
{
    /** @var string */
    private $property;

    /** @var string */
    private $method;

    /** @var string */
    private $returnType;

    public function __construct(string $property, string $method, string $returnType)
    {
        $this->property = $property;
        $this->method = $method;
        $this->returnType = $returnType;
    }

    /**
     * {@inheritdoc}
     */
    public function leaveNode(Node $node)
    {
        if (!$node instanceof Node\Stmt\Class_) {
            return null;
        }

        $node->stmts[] = $this->buildMethod($this->makePHPDoc($node->extends->parts[0]));

        return $node;
    }

    private function buildMethod(Doc $doc): Node\Stmt\ClassMethod
    {
        $method = new Method($this->method);
        $method->makeProtected();
        $method->setReturnType($this->returnType);
        $method->setDocComment($doc);
        $method->addStmt(
            new Node\Stmt\Return_(new Node\Expr\PropertyFetch(new Node\Expr\Variable("this"), $this->property))
        );

        return $method->getNode();
    }

    private function makePHPDoc(string $parent): Doc
    {
        return new Doc($this->docLines($parent));
    }

    private function docLines(string $parent): string
    {
        $lines = [
            "/**",
            sprintf(" * @return %s|%s", $parent, $this->returnType),
            " */"
        ];

        return join("\n", $lines);
    }
}