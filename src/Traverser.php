<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise;

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

    public function traverseFilename(string $filename, NodeVisitor ...$visitors)
    {
        return $this->makeTraverser(...$visitors)->traverse($this->parseNodes($filename));
    }

    public function traverseClonedNodes(array $nodes, NodeVisitor ...$visitors)
    {
        return $this->makeTraverser(...$visitors)->traverse($this->cloneNodes($nodes));
    }

    private function parseNodes(string $filename)
    {
        return $this->parser->parse(file_get_contents($filename));
    }

    private function cloneNodes(array $nodes)
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