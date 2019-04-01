<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\Parser;
use PhpParser\ParserFactory;

class Traverser
{
    /** @var Parser */
    private $parser;

    public function __construct(Parser $parser = null)
    {
        $this->parser = $parser ?? (new ParserFactory())->create(ParserFactory::ONLY_PHP7);
    }

    /**
     * @param string      $filename
     * @param NodeVisitor ...$visitors
     *
     * @return Node[]
     */
    public function traverseFilename(string $filename, NodeVisitor ...$visitors): array
    {
        return $this->makeTraverser(...$visitors)->traverse($this->parseNodes($filename));
    }

    /**
     * @param Node\Stmt[] $nodes
     * @param NodeVisitor ...$visitors
     *
     * @return Node[]
     */
    public function traverseClonedNodes(array $nodes, NodeVisitor ...$visitors): array
    {
        return $this->makeTraverser(...$visitors)->traverse($this->cloneNodes($nodes));
    }

    /**
     * @param string $filename
     *
     * @return Node\Stmt[]|null
     */
    private function parseNodes(string $filename): ?array
    {
        return $this->parser->parse(file_get_contents($filename));
    }

    /**
     * @param Node[] $nodes
     *
     * @return Node[]
     */
    private function cloneNodes(array $nodes): array
    {
        return $this->makeTraverser(new NodeVisitor\CloningVisitor())->traverse($nodes);
    }

    private function makeTraverser(NodeVisitor ...$visitors): NodeTraverser
    {
        $traverser = new NodeTraverser();
        foreach ($visitors as $visitor) {
            $traverser->addVisitor($visitor);
        }

        return $traverser;
    }
}