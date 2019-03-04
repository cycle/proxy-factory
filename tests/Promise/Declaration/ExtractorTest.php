<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests\Declaration;

use Cycle\ORM\Promise\Declaration\Declaration;
use Cycle\ORM\Promise\Declaration\Extractor;
use Cycle\ORM\Promise\Tests\Declaration\Fixtures\Entity;
use Cycle\ORM\Promise\Tests\Declaration\Fixtures\EntityWithConstructor;
use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;

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
        $methods = [];
        foreach ($this->getDeclaration(Entity::class)->methods as $method) {
            $methods[] = $method->name->name;
        }
        $this->assertSame(['public', 'protected'], $methods);

        //__construct is not listed
        $this->assertSame(['public', 'protected'], $this->getDeclaration(EntityWithConstructor::class)->properties);
    }

    private function getDeclaration(string $class): Declaration
    {
        return $this->extractor()->extract($class);
    }

    private function extractor(): Extractor
    {
        $container = new Container();

        return $container->get(Extractor::class);
    }
}