<?php
declare(strict_types=1);

namespace Spiral\Cycle\Promise;

use PhpParser\Lexer;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;
use Spiral\Cycle\ORMInterface;
use Spiral\Cycle\Promise\Declaration;
use Spiral\Cycle\Select\SourceFactoryInterface;

class ProxyCreator
{
    private const PROPERTY = '__resolver';

    private const DEPENDENCIES = [
        'orm'    => ORMInterface::class,
        'source' => SourceFactoryInterface::class,
        'target' => 'string',
        'scope'  => 'array'
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
        $schema = new Declaration\Schema($class, $as);

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

        $nodes = $this->getNodes(__DIR__ . DIRECTORY_SEPARATOR . 'Proxy.stub');
        $output = $this->traverser->traverseClonedNodes($nodes, ...$visitors);

        return $this->printer->printFormatPreserving(
            $output,
            $nodes,
            $this->lexer->getTokens()
        );
    }

    private function useStmts(Declaration\Schema $schema): array
    {
        if ($schema->class->namespace !== $schema->extends->namespace) {
            return [$schema->extends->getNamespacesName()];
        }

        return [];
    }

    private function propertyName(Declaration\Declaration $declaration): string
    {
        return $this->resolver->resolve($declaration->properties, self::PROPERTY);
    }

    private function propertyType(): string
    {
        return Utils::shortName(PromiseResolver::class);
    }

    private function getNodes(string $stub)
    {
        return $this->parser->parse(file_get_contents($stub));
    }
}