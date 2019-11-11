<?php

/**
 * Spiral Framework. Cycle ProxyFactory
 *
 * @license MIT
 * @author  Valentin V (vvval)
 */

declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests\Declaration;

use Cycle\ORM\Promise\Declaration\Extractor;
use Cycle\ORM\Promise\Declaration\Structure;
use Cycle\ORM\Promise\Tests\Declaration\Fixtures\Entity;
use Cycle\ORM\Promise\Tests\Declaration\Fixtures\EntityWithConstructor;
use Cycle\ORM\Promise\Tests\Fixtures\ChildEntity;
use Cycle\ORM\Promise\Tests\Fixtures\ParentEntity;
use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;

class ExtractorTest extends TestCase
{
    /**
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function testExtractProperties(): void
    {
        $extracted = $this->getDeclaration(ChildEntity::class)->properties();
        sort($extracted);

        $expected = ['public', 'protected', 'ownProperty', 'resolver'];
        sort($expected);

        $this->assertSame($expected, $extracted);
    }

    /**
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function testExtractParentMethods(): void
    {
        $extracted = [];
        foreach ($this->getDeclaration(ChildEntity::class)->methods as $method) {
            $extracted[$method->name->name] = $method->name->name;
        }

        $this->assertArrayHasKey('getParentProp', $extracted);
        $this->assertArrayHasKey('parentProtectedProp', $extracted);
    }

    /**
     * @throws \ReflectionException
     * @throws \Throwable
     * @throws \Throwable
     */
    public function testExtractMethods(): void
    {
        $methods = [];
        foreach ($this->getDeclaration(Entity::class)->methods as $method) {
            $methods[] = $method->name->name;
        }
        $this->assertSame(['public', 'protected'], $methods);

        //__construct is not listed
        $this->assertSame(['public', 'protected'], $this->getDeclaration(EntityWithConstructor::class)->properties());
    }

    /**
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function testSelfReturnTypes(): void
    {
        $extracted = [];
        foreach ($this->getDeclaration(ChildEntity::class)->methods as $method) {
            $extracted[$method->name->name] = $method->returnType->name;
        }

        $this->assertNotContains('self', $extracted);
        $this->assertContains('\\' . ChildEntity::class, $extracted);
        $this->assertContains('\\' . ParentEntity::class, $extracted);
    }

    /**
     * @param string $class
     * @return \Cycle\ORM\Promise\Declaration\Structure
     * @throws \ReflectionException
     * @throws \Throwable
     */
    private function getDeclaration(string $class): Structure
    {
        return $this->extractor()->extract(new \ReflectionClass($class));
    }

    /**
     * @return \Cycle\ORM\Promise\Declaration\Extractor
     * @throws \Throwable
     */
    private function extractor(): Extractor
    {
        $container = new Container();

        return $container->get(Extractor::class);
    }
}
