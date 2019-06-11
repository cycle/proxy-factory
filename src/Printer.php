<?php
/**
 * Spiral Framework.
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */
declare(strict_types=1);

namespace Cycle\ORM\Promise;

use Cycle\ORM\Promise\Declaration;
use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;

final class Printer
{

    private const LOADED_METHOD  = '__loaded';
    private const ROLE_METHOD    = '__role';
    private const SCOPE_METHOD   = '__scope';
    private const RESOLVE_METHOD = '__resolve';

    private const PROMISE_METHODS = [
        self::LOADED_METHOD  => 'bool',
        self::ROLE_METHOD    => 'string',
        self::SCOPE_METHOD   => 'array',
        self::RESOLVE_METHOD => null,
    ];

    /** @var Traverser */
    private $traverser;

    /** @var Declaration\Extractor */
    private $extractor;

    /** @var Stubs */
    private $stubs;

    /** @var Schema */
    private $schema;

    /** @var Lexer */
    private $lexer;

    /** @var Parser */
    private $parser;

    /** @var PrettyPrinterAbstract */
    private $printer;

    public function __construct(Traverser $traverser, Declaration\Extractor $extractor, Stubs $stubs, Schema $schema)
    {
        $this->traverser = $traverser;
        $this->extractor = $extractor;
        $this->stubs = $stubs;
        $this->schema = $schema;

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
     * @param \ReflectionClass                 $reflection
     * @param Declaration\DeclarationInterface $class
     * @param Declaration\DeclarationInterface $parent
     * @return string
     *
     * @throws \Cycle\ORM\Promise\ProxyFactoryException
     */
    public function make(\ReflectionClass $reflection, Declaration\DeclarationInterface $class, Declaration\DeclarationInterface $parent): string
    {
        $structure = $this->extractor->extract($reflection);
        foreach ($structure->methodNames() as $name) {
            if (array_key_exists($name, self::PROMISE_METHODS)) {
                throw new ProxyFactoryException("Promise method `$name` already defined.");
            }
        }

        $property = $this->schema->resolverPropertyName($structure);
        $unsetPropertiesConst = $this->schema->unsetPropertiesConstName($structure);

        $visitors = [
            new Visitor\AddUseStmts($this->schema->useStmts($class, $parent)),
            new Visitor\UpdateNamespace($class->getNamespaceName()),
            new Visitor\DeclareClass($class->getShortName(), $parent->getShortName(), Utils::shortName(PromiseInterface::class)),
            new Visitor\AddUnsetPropertiesConst($unsetPropertiesConst, $structure->properties),
            new Visitor\AddResolverProperty($property, $this->schema->propertyType(), $parent->getShortName()),
            new Visitor\AddInitMethod(
                $property,
                $this->schema->propertyType(),
                Schema::INIT_DEPENDENCIES,
                $this->schema->unsetPropertiesConstName($structure),
                $this->schema->initMethodName($structure)
            ),
            new Visitor\AddMagicCloneMethod($property, $structure->hasClone),
            new Visitor\AddMagicGetMethod($property, self::RESOLVE_METHOD),
            new Visitor\AddMagicSetMethod($property, self::RESOLVE_METHOD),
            new Visitor\AddMagicIssetMethod($property, self::RESOLVE_METHOD, $unsetPropertiesConst),
            new Visitor\AddMagicUnset($property, self::RESOLVE_METHOD, $unsetPropertiesConst),
            new Visitor\AddMagicDebugInfoMethod(
                $property,
                self::RESOLVE_METHOD,
                self::LOADED_METHOD,
                self::ROLE_METHOD,
                self::SCOPE_METHOD,
                $structure->properties
            ),
            new Visitor\UpdatePromiseMethods($property),
            new Visitor\AddProxiedMethods($property, $structure->methods, self::RESOLVE_METHOD),
        ];

        foreach (self::PROMISE_METHODS as $method => $returnType) {
            $visitors[] = new Visitor\AddPromiseMethod($property, $method, $returnType);
        }

        $nodes = $this->getNodesFromStub();
        $output = $this->traverser->traverseClonedNodes($nodes, ...$visitors);

        return $this->printer->printFormatPreserving(
            $output,
            $nodes,
            $this->lexer->getTokens()
        );
    }

    /**
     * @return Node\Stmt[]
     */
    private function getNodesFromStub(): array
    {
        return $this->parser->parse($this->stubs->getContent()) ?? [];
    }
}