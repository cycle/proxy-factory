<?php
declare(strict_types=1);

namespace Spiral\Cycle\Promise\Example;

use Spiral\Cycle\ORMInterface;
use Spiral\Cycle\Promise\PromiseInterface;
use Spiral\Cycle\Promise\PromiseResolver;
use Spiral\Cycle\Promise\ResolverTrait;
use Spiral\Cycle\Select\SourceFactoryInterface;

class UserProxy extends User implements PromiseInterface
{
    /** @var PromiseResolver */
    private $__resolver;

    /**
     * @param ORMInterface           $orm
     * @param SourceFactoryInterface $source
     * @param string                 $target
     * @param array                  $scope
     */
    public function __construct(ORMInterface $orm, SourceFactoryInterface $source, string $target, array $scope)
    {
        $this->__resolver = new PromiseResolver($orm, $source, $target, $scope);

        parent::__construct();
    }

    public function __loaded(): bool
    {
        return $this->__resolver->__loaded();
    }

    public function __role(): string
    {
        return $this->__resolver->__role();
    }

    public function __scope(): array
    {
        return $this->__resolver->__scope();
    }

    public function __resolve()
    {
        return $this->__resolver->__resolve();
    }

    public function getID()
    {
        return $this->__resolve()->getID();
    }

    public function addComment(Comment $c)
    {
        return $this->__resolve()->addComment($c);
    }
}