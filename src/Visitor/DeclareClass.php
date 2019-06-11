<?php
/**
 * Spiral Framework.
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */
declare(strict_types=1);

namespace Cycle\ORM\Promise\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * Declare proxy class, add extends and implements declarations
 */
final class DeclareClass extends NodeVisitorAbstract
{
    /** @var string */
    private $name;

    /** @var string|null */
    private $extends;

    /** @var string */
    private $implements;

    public function __construct(string $name, ?string $extends, string $implements)
    {
        $this->name = $name;
        $this->extends = $extends;
        $this->implements = $implements;
    }

    /**
     * {@inheritdoc}
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Class_) {
            if ($this->extends !== null) {
                $node->extends = new Node\Name($this->extends);
            }

            $node->name->name = $this->name;
            if ($this->canBeImplemented($node)) {
                $node->implements[] = new Node\Name($this->implements);
            }
        }

        return null;
    }

    private function canBeImplemented(Node\Stmt\Class_ $node): bool
    {
        foreach ($node->implements as $implement) {
            $name = join('\\', $implement->parts);
            if ($name === $this->implements) {
                return false;
            }
        }

        return true;
    }
}