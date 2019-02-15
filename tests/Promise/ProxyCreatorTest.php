<?php
declare(strict_types=1);

namespace Spiral\Cycle\Promise\Tests;

use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;
use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Cycle\ORM;
use Spiral\Cycle\ORMInterface;
use Spiral\Cycle\Promise\Declaration\Declaration;
use Spiral\Cycle\Promise\Declaration\Extractor;
use Spiral\Cycle\Promise\PromiseInterface;
use Spiral\Cycle\Promise\ProxyCreator;
use Spiral\Cycle\Promise\Tests\Fixtures\Entity;
use Spiral\Cycle\Promise\Utils;
use Spiral\Cycle\Select\SourceFactoryInterface;

class ProxyCreatorTest extends TestCase
{
    public function testDeclaration()
    {
        $output = $this->make(Entity::class);
        $output = ltrim($output, "<?php");

        $this->assertStringNotContainsString('abstract', $output);
        $this->assertStringContainsString(' extends ' . Utils::shortName(Entity::class), $output);
        $this->assertStringContainsString(' implements ' . Utils::shortName(PromiseInterface::class), $output);

        $container = new Container();
        $container->bind(ORMInterface::class, ORM::class);
        $container->bind(SourceFactoryInterface::class, ORM::class);

        eval($output);
        $class = Entity::class . "Proxy";
        $proxy = $container->make($class, ['target' => Entity::class, 'scope' => []]);
        $this->assertInstanceOf(Entity::class . "Proxy", $proxy);
        $this->assertInstanceOf(Entity::class, $proxy);
        $this->assertInstanceOf(PromiseInterface::class, $proxy);
    }

    private function make(string $class): string
    {
        return $this->proxyCreator()->make($class, $this->getDeclaration($class));
    }

    private function getDeclaration(string $class): Declaration
    {
        $class = new \ReflectionClass($class);

        return $this->extractor()->extract($class->getFileName());
    }

    private function extractor(): Extractor
    {
        $container = new Container();

        return $container->get(Extractor::class);
    }

    private function proxyCreator(): ProxyCreator
    {
        $container = new Container();
        $container->bind(PrettyPrinterAbstract::class, Standard::class);

        return $container->get(ProxyCreator::class);
    }
}