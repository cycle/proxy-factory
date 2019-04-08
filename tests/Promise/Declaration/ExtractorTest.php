<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests\Declaration;

use Cycle\ORM\Promise\Declaration\Structure;
use Cycle\ORM\Promise\Declaration\Extractor;
use Cycle\ORM\Promise\Tests\Declaration\Fixtures\Entity;
use Cycle\ORM\Promise\Tests\Declaration\Fixtures\EntityWithConstructor;
use Cycle\ORM\Promise\Tests\Fixtures\ChildEntity;
use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;

class ExtractorTest extends TestCase
{
    public function testExtractProperties(): void
    {
        $extracted = $this->getDeclaration(ChildEntity::class)->properties;
        sort($extracted);

        $expected = ['public', 'protected', 'ownProperty', '__resolver'];
        sort($expected);

        $this->assertSame($expected, $extracted);
    }

    public function testHasConstructor(): void
    {
        $this->assertFalse($this->getDeclaration(Entity::class)->hasConstructor);
        $this->assertTrue($this->getDeclaration(EntityWithConstructor::class)->hasConstructor);
    }

    public function testExtractParentMethods(): void
    {
        $extracted = [];
        foreach ($this->getDeclaration(ChildEntity::class)->methods as $method) {
            $extracted[$method->name->name] = $method->name->name;
        }

        $this->assertArrayHasKey('getParentProp', $extracted);
        $this->assertArrayHasKey('parentProtectedProp', $extracted);
    }

    public function testExtractMethods(): void
    {
        $methods = [];
        foreach ($this->getDeclaration(Entity::class)->methods as $method) {
            $methods[] = $method->name->name;
        }
        $this->assertSame(['public', 'protected'], $methods);

        //__construct is not listed
        $this->assertSame(['public', 'protected'], $this->getDeclaration(EntityWithConstructor::class)->properties);
    }

    private function getDeclaration(string $class): Structure
    {
        return $this->extractor()->extract($class);
    }

    private function extractor(): Extractor
    {
        $container = new Container();

        return $container->get(Extractor::class);
    }
}