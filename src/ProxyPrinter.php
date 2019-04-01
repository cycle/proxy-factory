<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\Promise\Declaration\Declaration;
use Cycle\ORM\Promise\Declaration\Extractor;
use Cycle\ORM\Promise\Declaration\Structure;
use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;

class ProxyPrinter
{
    private const PROPERTY = '__resolver';

    private const DEPENDENCIES = [
        'orm'   => ORMInterface::class,
        'role'  => 'string',
        'scope' => 'array'
    ];

    /** @var ConflictResolver */
    private $resolver;

    /** @var Traverser */
    private $traverser;

    /** @var Extractor */
    private $extractor;

    /** @var Lexer */
    private $lexer;

    /** @var Parser */
    private $parser;

    /** @var PrettyPrinterAbstract */
    private $printer;

    public function __construct(ConflictResolver $resolver, Traverser $traverser, Extractor $extractor)
    {
        $this->resolver = $resolver;
        $this->traverser = $traverser;
        $this->extractor = $extractor;

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

        $this->printer = new Standard();
    }

    /**
     *
     * @param Declaration $declaration
     *
     * @return string
     */
    public function make(Declaration $declaration): string
    {
        $structure = $this->extractor->extract($declaration->parent->getNamespacesName());

        $property = $this->propertyName($structure);

        $visitors = [
            new Visitor\AddUseStmts($this->useStmts($declaration)),
            new Visitor\UpdateNamespace($declaration->class->namespace),
            new Visitor\DeclareClass($declaration->class->name, $declaration->parent->name),
            new Visitor\AddResolverProperty($property, $this->propertyType(), $declaration->parent->name),
            new Visitor\UpdateConstructor($structure->hasConstructor, $property, $this->propertyType(), self::DEPENDENCIES),
            new Visitor\UpdatePromiseMethods($property),
            new Visitor\AddProxiedMethods($property, $structure->methods),
        ];

        $nodes = $this->getNodesFromStub();
        $output = $this->traverser->traverseClonedNodes($nodes, ...$visitors);

        return $this->printer->printFormatPreserving(
            $output,
            $nodes,
            $this->lexer->getTokens()
        );
    }

    private function propertyName(Structure $declaration): string
    {
        return $this->resolver->resolve($declaration->properties, self::PROPERTY);
    }

    private function useStmts(Declaration $schema): array
    {
        if ($schema->class->namespace !== $schema->parent->namespace) {
            return [$schema->parent->getNamespacesName()];
        }

        return [];
    }

    private function propertyType(): string
    {
        return Utils::shortName(PromiseResolver::class);
    }

    /**
     * @return Node\Stmt[]|null
     */
    private function getNodesFromStub(): ?array
    {
        return $this->parser->parse(file_get_contents($this->getStubFilename()));
    }

    private function getStubFilename(): string
    {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'proxy.stub';
    }
}