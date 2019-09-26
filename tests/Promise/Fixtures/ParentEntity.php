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

    protected function parentProtectedProp(): string
    {
        return 'childParentPropValue';
    }

    public function parentSelf(): self
    {
        return $this;
    }
}