<?php
declare(strict_types=1);

namespace Spiral\Cycle\Promise\Visitor;

use PhpParser\Builder\Method;
use PhpParser\Builder\Param;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Spiral\Cycle\Promise\Utils;

/**
 * Add constructor
 */
class AddConstructor extends NodeVisitorAbstract
{
    private const CONSTRUCTOR_METHOD_NAME = '__construct';

    /** @var string */
    private $property;

    /** @var string */
    private $propertyType;

    /** @var array */
    private $dependencies = [];

    public function __construct(string $property, string $propertyType, array $dependencies)
    {
        $this->property = $property;
        $this->propertyType = $propertyType;
        $this->dependencies = $dependencies;
    }

    /**
     * {@inheritdoc}
     */
    public function leaveNode(Node $node)
    {
        if (!$node instanceof Node\Stmt\Class_) {
            return null;
        }

        $node->stmts = Utils::injectValues($node->stmts, $this->definePlacementID($node), [$this->buildMethod()]);

        return $node;
    }

    private function definePlacementID(Node\Stmt\Class_ $node): int
    {
        foreach ($node->stmts as $index => $child) {
            if ($child instanceof Node\Stmt\ClassMethod) {
                return $index;
            }
        }

        return 0;
    }

    private function buildMethod(): Node\Stmt\ClassMethod
    {
        $constructor = new Method(self::CONSTRUCTOR_METHOD_NAME);
        $constructor->makePublic();
        $constructor->setDocComment($this->makePHPDoc());
        $constructor->addParams($this->makeParams());

        $constructor->addStmts([
            $this->makeResolverAssignment(),
            $this->makeParentConstructorCall()
        ]);

        return $constructor->getNode();
    }

    private function makePHPDoc(): Doc
    {
        return new Doc($this->docLines());
    }

    private function docLines(): string
    {
        $lines = ["/**"];
        foreach ($this->dependencies as $name => $type) {
            $lines[] = sprintf(" * @param %s $%s", Utils::shortName($type), $name);
        }
        $lines[] = " */";

        return join("\n", $lines);
    }

    /**
     * @return Node\Param[]
     */
    private function makeParams(): array
    {
        $params = [];
        foreach ($this->dependencies as $name => $type) {
            $param = new Param($name);
            $param->setType(new Node\Name(Utils::shortName($type)));

            $params[] = $param->getNode();
        }

        return $params;
    }

    private function makeResolverAssignment(): Node\Stmt\Expression
    {
        $prop = new Node\Expr\PropertyFetch(new Node\Expr\Variable("this"), $this->property);
        $instance = new Node\Expr\New_(new Node\Name($this->propertyType), $this->makeInstanceArgs());

        return new Node\Stmt\Expression(new Node\Expr\Assign($prop, $instance));
    }

    /**
     * @return Node\Arg[]
     */
    private function makeInstanceArgs(): array
    {
        $args = [];
        foreach ($this->dependencies as $name => $type) {
            $args[] = new Node\Arg(new Node\Expr\Variable($name));
        }

        return $args;
    }

    private function makeParentConstructorCall(): Node\Stmt\Expression
    {
        return new Node\Stmt\Expression(new Node\Expr\StaticCall(new Node\Name('parent'), self::CONSTRUCTOR_METHOD_NAME));
    }
}