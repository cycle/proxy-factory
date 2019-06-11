<?php
/**
 * Spiral Framework.
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */
declare(strict_types=1);

namespace Cycle\ORM\Promise\Printers;

use Cycle\ORM\Promise\Declaration;
use Cycle\ORM\Promise\Declaration\Structure;
use Cycle\ORM\Promise\PromiseInterface;
use Cycle\ORM\Promise\Schema;
use Cycle\ORM\Promise\Stubs;
use Cycle\ORM\Promise\Traverser;
use Cycle\ORM\Promise\Utils;
use Cycle\ORM\Promise\Visitor;
use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;

final class NullPromisePrinter
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

    public function __construct(Traverser $traverser, Stubs $stubs, Schema $schema)
    {
        $this->traverser = $traverser;
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
     * @param Declaration\Structure            $structure
     * @param Declaration\DeclarationInterface $class
     * @param Declaration\DeclarationInterface $parent
     * @return string
     */
    public function make(Structure $structure, Declaration\DeclarationInterface $class, Declaration\DeclarationInterface $parent): string
    {
        $property = $this->schema->resolverPropertyName(null);

        $visitors = [
            new Visitor\AddUseStmts($this->schema->useStmts($class, $parent)),
            new Visitor\UpdateNamespace($class->getNamespaceName()),
            new Visitor\DeclareClass($class->getShortName(), $parent->getShortName(), Utils::shortName(PromiseInterface::class)),
            new Visitor\AddResolverProperty($property, $this->schema->propertyType(), $parent->getShortName()),
            new Visitor\AddInitMethod(
                $property,
                $this->schema->propertyType(),
                Schema::INIT_DEPENDENCIES,
                null,
                $this->schema->initMethodName(null)
            ),
            new Visitor\NullVisitor\ThrowExceptionOnMethodCall('__clone'),
            new Visitor\NullVisitor\ThrowExceptionOnMethodCall('__get', ['name' => null]),
            new Visitor\NullVisitor\ThrowExceptionOnMethodCall('__set', ['name' => null, 'value' => null]),
            new Visitor\NullVisitor\ThrowExceptionOnMethodCall('__isset', ['name' => null]),
            new Visitor\NullVisitor\ThrowExceptionOnMethodCall('__unset', ['name' => null]),
            new Visitor\NullVisitor\ThrowExceptionOnMethodCall('__debugInfo'),
            new Visitor\UpdatePromiseMethods($property),
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