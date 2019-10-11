<?php

declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests\Fixtures;

class ParentEntity
{
    protected $protected;
    protected $__resolver;

    public function getParentProp(): string
    {
        return 'parentPropValue';
    }

    public function parentSelf(): self
    {
        return $this;
    }

    protected function parentProtectedProp(): string
    {
        return 'childParentPropValue';
    }
}
