<?php
declare(strict_types=1);

namespace Spiral\Cycle\Promise;

use PhpParser\Lexer;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\Parser;
use PhpParser\NodeTraverser;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;
use Spiral\Cycle\ORMInterface;
use Spiral\Cycle\Promise\Declaration\Declaration;
use Spiral\Cycle\Select\SourceFactoryInterface;

class ProxyCreator
{
    const PROXY_RESOLVER_PROPERTY = '__resolver';
    const PROXY_RESOLVER_CALL     = '__resolver';

    const PROXY_DEPENDENCIES = [
        ORMInterface::class,
        SourceFactoryInterface::class,
        PromiseInterface::class,
        ResolverTrait::class,
        PromiseResolver::class,
    ];

    const PROXY_CONSTRUCTOR_PARAMS = [
        'orm'    => ORMInterface::class,
        'source' => SourceFactoryInterface::class,
        'target' => 'string',
        'scope'  => 'array'
    ];

    /** @var ConflictResolver */
    private $resolver;

    /** @var Traverser */
    private $traverser;

    /** @var Parser */
    private $parser;

    /** @var Lexer */
    private $lexer;

    /** @var PrettyPrinterAbstract */
    private $printer;

    /** @var Traverser */
    private $cloner;

    public function __construct(ConflictResolver $resolver, Traverser $traverser)
    {
        $this->resolver = $resolver;
        $this->traverser = $traverser;

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

    /**
     * 1 add use statements
     *      use Spiral\Cycle\ORMInterface;
     *      use Spiral\Cycle\Promise\PromiseInterface;
     *      use Spiral\Cycle\Promise\ResolverTrait;
     *      use Spiral\Cycle\Select\SourceFactoryInterface;
     * 2 delete use statements
     * 3 declare class
     *      rename to <class name>+"Proxy"
     *      add "extends" declaration
     *      add "implements" declaration
     * 4 add "use ResolverTrait;"
     *
     * 5 add "__resolver" property with PHPDoc (with name conflict resolving)
     * 6 add "__constructor" with:
     *      proxy parameters
     *      $this->__resolver = new ProxyResolver assignment
     *      parent::__constructor() call
     * 7 replace all public/protected method calls with resolved stub
     * 8 add "__resolver()" method with PHPDoc (with name conflict resolving)
     *
     * @param string      $class
     * @param Declaration $declaration
     *
     * @return string
     */
    public function make(string $class, Declaration $declaration): string
    {
        $propertyName = $this->propertyName($declaration);
        $visitors = [
            new Visitor\AddUseStmts(self::PROXY_DEPENDENCIES),
            new Visitor\RemoveUseStmts(),
            new Visitor\DeclareClass(),
            new Visitor\RemoveProperties(),
            new Visitor\AddTrait(),
            new Visitor\AddResolverProperty($propertyName, $this->propertyType()),
            new Visitor\AddConstructor($propertyName, $this->propertyType(), self::PROXY_CONSTRUCTOR_PARAMS),
            new Visitor\ModifyProxyMethod(),
            new Visitor\AddResolverGetter($propertyName, $this->methodName($declaration), $this->propertyType()),
        ];

        $nodes = $this->getNodes($class);
        $output = $this->traverser->traverseClonedNodes($nodes, ...$visitors);

        return $this->printer->printFormatPreserving($output, $nodes, $this->lexer->getTokens());
    }

    private function propertyName(Declaration $declaration): string
    {
        return $this->resolver->resolve($declaration->properties, self::PROXY_RESOLVER_PROPERTY);
    }

    private function propertyType(): string
    {
        return Utils::shortName(PromiseResolver::class);
    }

    private function methodName(Declaration $declaration): string
    {
        return $this->resolver->resolve($declaration->methods, self::PROXY_RESOLVER_CALL);
    }

    private function getNodes(string $class)
    {
        return $this->parser->parse($this->getCode($class));
    }

    private function getCode(string $class): string
    {
        $class = new \ReflectionClass($class);

        return file_get_contents($class->getFileName());
    }
}