<?php
declare(strict_types=1);

namespace Spiral\Cycle\Tests\Fixtures;

use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;
use Spiral\Cycle\Mapper\Mapper;
use Spiral\Cycle\Promise\PromiseInterface;
use Spiral\Cycle\Promise\ProxyFactoryInterface;

class UserMapperWithProxy extends Mapper implements ProxyFactoryInterface
{
    /** @var Parser */
    private $parser;

    /** @var Lexer */
    private $lexer;

    /** @var null|Standard|PrettyPrinterAbstract */
    private $printer;

    /** @var NodeTraverser */
    private $cloner;

    public function __construct(\Spiral\Cycle\ORMInterface $orm, string $role)
    {
        parent::__construct($orm, $role);

        $lexer = new Lexer\Emulative([
            'usedAttributes' => [
                'comments',
                'startLine',
                'endLine',
                'startTokenPos',
                'endTokenPos',
            ],
        ]);

        $this->lexer = $lexer;
        $this->parser = new Parser\Php7($this->lexer);

        $this->cloner = new NodeTraverser();
        $this->cloner->addVisitor(new CloningVisitor());

        $this->printer = new Standard();
    }

    public function makeProxy(array $scope): ?PromiseInterface
    {
        return new UserProxy($this->orm, $this->role, $scope);
    }

    private function getCode(): string
    {
        $class = new \ReflectionClass($this->role);

        return file_get_contents($class->getFileName());
    }

    private function traverse(string $code)
    {
        $tr = new NodeTraverser();
//        $tr->addVisitor(new AddUse($definition));
//        $tr->addVisitor(new RemoveUse());
//        $tr->addVisitor(new RemoveTrait());
//        $tr->addVisitor(new AddProperty($definition));
//        $tr->addVisitor(new DefineConstructor());
//        $tr->addVisitor(new UpdateConstructor($definition));

        $nodes = $this->parser->parse($code);
        $tokens = $this->lexer->getTokens();

        $output = $tr->traverse($this->cloner->traverse($nodes));

        return $this->printer->printFormatPreserving($output, $nodes, $tokens);
    }
}