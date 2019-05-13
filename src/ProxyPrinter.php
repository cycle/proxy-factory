<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\Promise\Declaration\DeclarationInterface;
use Cycle\ORM\Promise\Declaration\Extractor;
use Cycle\ORM\Promise\Declaration\Structure;
use Cycle\ORM\Promise\Visitor\AddMagicClone;
use Cycle\ORM\Promise\Visitor\AddMagicIsset;
use Cycle\ORM\Promise\Visitor\AddMagicGet;
use Cycle\ORM\Promise\Visitor\AddMagicSet;
use Cycle\ORM\Promise\Visitor\AddMagicUnset;
use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;

class ProxyPrinter
{
    private const RESOLVER_PROPERTY = '__resolver';
    private const UNSET_PROPERTIES  = 'UNSET_PROPERTIES';
    private const RESOLVE_METHOD    = '__resolve';

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

    /** @var Stubs */
    private $stubs;

    public function __construct(ConflictResolver $resolver, Traverser $traverser, Extractor $extractor, Stubs $stubs)
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
        $this->stubs = $stubs;
    }

    public function make(\ReflectionClass $reflection, DeclarationInterface $class, DeclarationInterface $parent): string
    {
        $structure = $this->extractor->extract($reflection);

        $property = $this->resolverPropertyName($structure);
        $unsetPropertiesConst = $this->unsetPropertiesConstName($structure);

        $visitors = [
            new Visitor\AddUseStmts($this->useStmts($class, $parent)),
            new Visitor\UpdateNamespace($class->getNamespaceName()),
            new Visitor\DeclareClass($class->getShortName(), $parent->getShortName()),
            new Visitor\AddUnsetPropertiesConst($unsetPropertiesConst, $structure->properties),
            new Visitor\AddResolverProperty($property, $this->propertyType(), $parent->getShortName()),
            new Visitor\UpdateConstructor(
                $structure->hasConstructor,
                $property,
                $this->propertyType(),
                self::DEPENDENCIES,
                $this->unsetPropertiesConstName($structure)
            ),
            new AddMagicClone($property, $structure->hasClone),
            new AddMagicGet($property, self::RESOLVE_METHOD),
            new AddMagicSet($property, self::RESOLVE_METHOD),
            new AddMagicIsset($property, self::RESOLVE_METHOD, $unsetPropertiesConst),
            new AddMagicUnset($property, self::RESOLVE_METHOD, $unsetPropertiesConst),
            new Visitor\UpdatePromiseMethods($property),
            new Visitor\AddProxiedMethods($property, $structure->methods, self::RESOLVE_METHOD),
        ];

        $nodes = $this->getNodesFromStub();
        $output = $this->traverser->traverseClonedNodes($nodes, ...$visitors);

        return $this->printer->printFormatPreserving(
            $output,
            $nodes,
            $this->lexer->getTokens()
        );
    }

    private function resolverPropertyName(Structure $structure): string
    {
        return $this->resolver->resolve($structure->properties, self::RESOLVER_PROPERTY)->fullName();
    }

    private function unsetPropertiesConstName(Structure $structure): string
    {
        return $this->resolver->resolve($structure->constants, self::UNSET_PROPERTIES)->fullName('_');
    }

    private function useStmts(DeclarationInterface $class, DeclarationInterface $parent): array
    {
        $useStmts = [];
        if ($class->getNamespaceName() !== $parent->getNamespaceName()) {
            $useStmts[] = $parent->getFullName();
        }

        $useStmts[] = PromiseResolver::class;

        return $useStmts;
    }

    private function propertyType(): string
    {
        return Utils::shortName(PromiseResolver::class);
    }

    /**
     * @return Node\Stmt[]
     */
    private function getNodesFromStub(): array
    {
        return $this->parser->parse($this->stubs->getContent()) ?? [];
    }
}