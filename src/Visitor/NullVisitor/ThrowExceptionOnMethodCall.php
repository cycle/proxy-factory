<?php
/**
 * Spiral Framework.
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */
declare(strict_types=1);

namespace Cycle\ORM\Promise\Visitor\NullVisitor;

use Cycle\ORM\Promise\Expressions;
use Cycle\ORM\Promise\ProxyFactoryException;
use Cycle\ORM\Promise\Utils;
use PhpParser\Builder;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class ThrowExceptionOnMethodCall extends NodeVisitorAbstract
{
    /** @var string */
    private $method;

    /** @var array */
    private $params;

    public function __construct(string $method, array $params = [])
    {
        $this->method = $method;
        $this->params = $params;
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Class_) {
            $method = new Builder\Method($this->method);
            $method->makePublic();

            foreach ($this->params as $name => $type) {
                $param = $method->addParam(new Builder\Param($name));
                if ($type !== null) {
                    $param->setReturnType($type);
                }
            }

            $method->addStmt(Expressions::throwException(
                Utils::shortName(ProxyFactoryException::class),
                "Called `$this->method` method of a null object."
            ));

            $node->stmts[] = $method->getNode();
        }

        return null;
    }
}