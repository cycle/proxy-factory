<?php
declare(strict_types=1);

namespace Spiral\Cycle\Promise\Tests\Declaration;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Cycle\Promise\Declaration\Declaration;
use Spiral\Cycle\Promise\Declaration\Extractor;
use Spiral\Cycle\Promise\Tests\Declaration\Fixtures\Entity;
use Spiral\Cycle\Promise\Tests\Declaration\Fixtures\EntityWithConstructor;

class ExtractorTest extends TestCase
{
    public function testExtractProperties()
    {
        $this->assertSame(['public', 'protected'], $this->getDeclaration(Entity::class)->properties);
    }

    public function testHasConstructor()
    {
        $this->assertFalse($this->getDeclaration(Entity::class)->hasConstructor);
        $this->assertTrue($this->getDeclaration(EntityWithConstructor::class)->hasConstructor);
    }

    public function testExtractMethods()
    {
        $this->assertSame(['public', 'protected'], $this->getDeclaration(Entity::class)->methods);

        //__construct is not listed
        $this->assertSame(['public', 'protected'], $this->getDeclaration(EntityWithConstructor::class)->properties);
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