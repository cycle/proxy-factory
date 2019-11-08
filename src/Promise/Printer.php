<?php

/**
 * Spiral Framework. Cycle ProxyFactory
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */

declare(strict_types=1);

namespace Cycle\ORM\Promise;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\Promise\Declaration;
use Cycle\ORM\Promise\Exception\ProxyFactoryException;
use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;

final class Printer
{
    public const RESOLVER_PROPERTY      = 'resolver';
    public const UNSET_PROPERTIES_CONST = 'UNSET_PROPERTIES';
    public const INIT_METHOD            = '__init';

    private const LOADED_METHOD  = '__loaded';
    private const ROLE_METHOD    = '__role';
    private const SCOPE_METHOD   = '__scope';
    private const RESOLVE_METHOD = '__resolve';

    private const DEPENDENCIES = [
        'orm'   => ORMInterface::class,
        'role'  => 'string',
        'scope' => 'array'
    ];

    private const USE_STMTS = [
        PromiseInterface::class,
        Resolver::class,
        ProxyFactoryException::class,
        ORMInterface::class
    ];

    private const PROMISE_METHODS = [
        self::LOADED_METHOD  => 'bool',
        self::ROLE_METHOD    => 'string',
        self::SCOPE_METHOD   => 'array',
        self::RESOLVE_METHOD => null,
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

    /** @var Stubs */
    private $stubs;

    /**
     * @param Declaration\Extractor $extractor
     * @param ConflictResolver      $resolver
     * @param Traverser             $traverser
     * @param Stubs                 $stubs
     */
    public function __construct(
        Declaration\Extractor $extractor,
        Traverser $traverser,
        ConflictResolver $resolver,
        Stubs $stubs
    ) {
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
        $this->stubs = $stubs ?? new Stubs();
    }

    /**
     * @param \ReflectionClass                 $reflection
     * @param Declaration\DeclarationInterface $class
     * @param Declaration\DeclarationInterface $parent
     * @return string
     *
     * @throws ProxyFactoryException
     */
    public function make(
        \ReflectionClass $reflection,
        Declaration\DeclarationInterface $class,
        Declaration\DeclarationInterface $parent
    ): string {
        $structure = $this->extractor->extract($reflection);
        foreach ($structure->methodNames() as $name) {
            if (array_key_exists($name, self::PROMISE_METHODS)) {
                throw new ProxyFactoryException("Promise method `$name` already defined.");
            }
        }

        $property = $this->resolverPropertyName($structure);
        $unsetPropertiesConst = $this->unsetPropertiesConstName($structure);

        $visitors = [
            new Visitor\AddUseStmts($this->useStmts($class, $parent)),
            new Visitor\UpdateNamespace($class->getNamespaceName()),
            new Visitor\DeclareClass(
                $class->getShortName(),
                $parent->getShortName(),
                shortName(PromiseInterface::class)
            ),
            new Visitor\AddUnsetPropertiesConst($unsetPropertiesConst, $structure->properties),
            new Visitor\AddResolverProperty($property, $this->propertyType(), $parent->getShortName()),
            new Visitor\AddInitMethod(
                $property,
                $this->propertyType(),
                self::DEPENDENCIES,
                $this->unsetPropertiesConstName($structure),
                $this->initMethodName($structure)
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
     * @param Declaration\Structure $structure
     * @return string
     */
    public function initMethodName(Declaration\Structure $structure): string
    {
        return $this->resolver->resolve($structure->methodNames(), self::INIT_METHOD)->fullName();
    }

    /**
     * @param Declaration\Structure $structure
     * @return string
     */
    private function resolverPropertyName(Declaration\Structure $structure): string
    {
        return $this->resolver->resolve($structure->properties, self::RESOLVER_PROPERTY)->fullName();
    }

    /**
     * @param Declaration\Structure $structure
     * @return string
     */
    private function unsetPropertiesConstName(Declaration\Structure $structure): string
    {
        return $this->resolver->resolve($structure->constants, self::UNSET_PROPERTIES_CONST)->fullName('_');
    }

    /**
     * @param Declaration\DeclarationInterface $class
     * @param Declaration\DeclarationInterface $parent
     * @return array
     */
    private function useStmts(Declaration\DeclarationInterface $class, Declaration\DeclarationInterface $parent): array
    {
        $useStmts = self::USE_STMTS;
        if ($class->getNamespaceName() !== $parent->getNamespaceName()) {
            $useStmts[] = $parent->getFullName();
        }

        return $useStmts;
    }

    /**
     * @return string
     */
    private function propertyType(): string
    {
        return shortName(Resolver::class);
    }

    /**
     * @return Node\Stmt[]
     */
    private function getNodesFromStub(): array
    {
        return $this->parser->parse($this->stubs->getContent()) ?? [];
    }
}
