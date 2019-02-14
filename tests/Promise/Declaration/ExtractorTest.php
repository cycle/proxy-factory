<?php
declare(strict_types=1);

namespace Spiral\Cycle\Promise\Tests\Declaration;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Cycle\Promise\Declaration\Declaration;
use Spiral\Cycle\Promise\Declaration\Extractor;
use Spiral\Cycle\Promise\Tests\Declaration\Fixtures\Entity;

class ExtractorTest extends TestCase
{
    public function testExtractProperties()
    {
        $declaration = $this->getDeclaration(Entity::class);
        $this->assertSame(['public', 'protected'], $declaration->properties);
    }

    public function testExtractMethods()
    {
        $declaration = $this->getDeclaration(Entity::class);
        $this->assertSame(['public', 'protected'], $declaration->methods);
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
}