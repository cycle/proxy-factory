<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\Promise\Declaration;
use PhpParser\Lexer;
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

    /** @var Declaration\Extractor */
    private $extractor;

    /** @var Lexer */
    private $lexer;

    /** @var Parser */
    private $parser;

    /** @var PrettyPrinterAbstract */
    private $printer;

    public function __construct(ConflictResolver $resolver, Traverser $traverser, Declaration\Extractor $extractor)
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
     * @param string $class
     * @param string $as
     *
     * @return string
     */
    public function make(string $class, string $as): string
    {
        $declaration = $this->extractor->extract($class);
        $schema = new Declaration\Declaration($class, $as);

        $property = $this->propertyName($declaration);

        $visitors = [
            new Visitor\AddUseStmts($this->useStmts($schema)),
            new Visitor\UpdateNamespace($schema->class->namespace),
            new Visitor\DeclareClass($schema->class->class, $schema->extends->class),
            new Visitor\AddResolverProperty($property, $this->propertyType(), $schema->extends->class),
            new Visitor\UpdateConstructor($declaration->hasConstructor, $property, $this->propertyType(), self::DEPENDENCIES),
            new Visitor\UpdatePromiseMethods($property),
            new Visitor\AddProxiedMethods($property, $declaration->methods),
        ];

        $nodes = $this->getNodesFromStub();
        $output = $this->traverser->traverseClonedNodes($nodes, ...$visitors);

        return $this->printer->printFormatPreserving(
            $output,
            $nodes,
            $this->lexer->getTokens()
        );
    }

    private function propertyName(Declaration\Structure $declaration): string
    {
        return $this->resolver->resolve($declaration->properties, self::PROPERTY);
    }

    private function useStmts(Declaration\Declaration $schema): array
    {
        if ($schema->class->namespace !== $schema->extends->namespace) {
            return [$schema->extends->getNamespacesName()];
        }

        return [];
    }

    private function propertyType(): string
    {
        return Utils::shortName(PromiseResolver::class);
    }

    private function getNodesFromStub()
    {
        return $this->parser->parse(file_get_contents($this->getStubFilename()));
    }

    private function getStubFilename(): string
    {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'proxy.stub';
    }
}