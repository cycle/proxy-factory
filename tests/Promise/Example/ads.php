<?php
declare(strict_types=1);

namespace A\B\C;

use Spiral\Cycle\ORMInterface;
use Spiral\Cycle\Promise\PromiseInterface;
use Spiral\Cycle\Promise\PromiseResolver;
use Spiral\Cycle\Select\SourceFactoryInterface;
use Spiral\Cycle\Promise\Tests\Fixtures\Entity;

class EntityProxy34 extends Entity implements PromiseInterface
{
    /** @var PromiseResolver|Entity */
    private $__resolver2;
    /**
     * @param ORMInterface           $orm
     * @param SourceFactoryInterface $source
     * @param string                 $target
     * @param array                  $scope
     */
    public function __construct(ORMInterface $orm, SourceFactoryInterface $source, string $target, array $scope)
    {
        $this->__resolver2 = new PromiseResolver($orm, $source, $target, $scope);
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    public function __loaded() : bool
    {
        return $this->__resolver2->__loaded();
    }

    /**
     * {@inheritdoc}
     */
    public function __role() : string
    {
        return $this->__resolver2->__role();
    }

    /**
     * {@inheritdoc}
     */
    public function __scope() : array
    {
        return $this->__resolver2->__scope();
    }

    /**
     * {@inheritdoc}
     */
    public function __resolve()
    {
        return $this->__resolver2->__resolve();
    }
    /**
     * {@inheritdoc}
     */
    public function public() : ?string
    {
        return $this->__resolver2->public();
    }
    /**
     * {@inheritdoc}
     */
    public function __resolver()
    {
        return $this->__resolver2->__resolver();
    }
    /**
     * {@inheritdoc}
     */
    protected function protected() : array
    {
        return $this->__resolver2->protected();
    }
}