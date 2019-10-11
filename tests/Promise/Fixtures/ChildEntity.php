<?php

/**
 * Spiral Framework. Cycle ProxyFactory
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */
declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests\Fixtures;

use Cycle\ORM\Promise\Tests\Declaration\Fixtures\EntityWithConstructor as EWC;

class ChildEntity extends ParentEntity
{
    public $public;
    protected $ownProperty;

    public function childProp(): string
    {
        return 'childPropValue';
    }

    public function childSelf(): self
    {
        return $this;
    }

    protected function childProtectedProp(): \stdClass
    {
        return new \stdClass();
    }

    protected function childExampleObj1(): \Cycle\ORM\Promise\Tests\Declaration\Fixtures\Entity
    {
        return new \Cycle\ORM\Promise\Tests\Declaration\Fixtures\Entity();
    }

    protected function childExampleObj2(): EWC
    {
        return new EWC();
    }

    protected function childExampleObj3(): EWC
    {
        return new \Cycle\ORM\Promise\Tests\Declaration\Fixtures\EntityWithConstructor();
    }
}
