<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\Promise\Declaration\DeclarationInterface;
use Cycle\ORM\Promise\Declaration\Extractor;
use Cycle\ORM\Promise\Declaration\Structure;
use PhpParser\Builder\Use_;
use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;

class ProxyPrinter
{
    private const RESOLVER_PROPERTY = '__resolver';
    private const UNSET_PROPERTIES  = 'UNSET_PROPERTIES';
    private const RESOLVE_METHOD    = '__resolve';
    private const INIT_METHOD       = '__init';

    private const DEPENDENCIES = [
        'orm'   => ORMInterface::class,
        'role'  => 'string',
        'scope' => 'array'
    ];

    private const USE_STMTS = [
        PromiseInterface::class,
        PromiseResolver::class,
        PromiseException::class,
        ORMInterface::class
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
            new Visitor\DeclareClass($class->getShortName(), $parent->getShortName(), Utils::shortName(PromiseInterface::class)),
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
            new Visitor\AddMagicDebugInfoMethod($property, self::RESOLVE_METHOD, $structure->properties),
            new Visitor\UpdatePromiseMethods($property),
            new Visitor\AddPromiseMethod($property, '__loaded', 'bool'),
            new Visitor\AddPromiseMethod($property, '__role', 'string'),
            new Visitor\AddPromiseMethod($property, '__scope', 'array'),
            new Visitor\AddPromiseMethod($property, '__resolve'),
            new Visitor\AddProxiedMethods($property, $structure->methods, self::RESOLVE_METHOD),
        ];

        $nodes = $this->parser->parse(file_get_contents($reflection->getFileName()));
        $cloner = new NodeTraverser();
        $cloner->addVisitor(new CloningVisitor());
        $tr = new NodeTraverser();
        $output = $tr->traverse($cloner->traverse($nodes));
        $in = null;
        foreach ($output as $i => $o) {
            if ($o instanceof Node\Stmt\Class_) {
                $in = $i;
                break;
            }
        }

        if ($in !== null) {
            $use = new Use_(new Node\Name(self::class), Node\Stmt\Use_::TYPE_NORMAL);
            $output = Utils::injectValues($output, $in, [$use->getNode()]);
        }

//        dump($this->printer->printFormatPreserving(
//            $output,
//            $nodes,
//            $this->lexer->getTokens()
//        ));

        $nodes = $this->getNodesFromStub();
        $output = $this->traverser->traverseClonedNodes($nodes, ...$visitors);

        return $this->printer->printFormatPreserving(
            $output,
            $nodes,
            $this->lexer->getTokens()
        );
    }

    public function initMethodName(Structure $structure): string
    {
        return $this->resolver->resolve($structure->methodNames(), self::INIT_METHOD)->fullName();
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
        $useStmts = self::USE_STMTS;
        if ($class->getNamespaceName() !== $parent->getNamespaceName()) {
            $useStmts[] = $parent->getFullName();
        }

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