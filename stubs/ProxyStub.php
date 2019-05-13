<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Example;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\Promise\PromiseInterface;

class ProxyStub implements PromiseInterface
{
    /**
     * @param ORMInterface $orm
     * @param string       $role
     * @param array        $scope
     */
    public function __construct(ORMInterface $orm, string $role, array $scope)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function __loaded(): bool
    {
    }

    /**
     * {@inheritdoc}
     */
    public function __role(): string
    {
    }

    /**
     * {@inheritdoc}
     */
    public function __scope(): array
    {
    }

    /**
     * {@inheritdoc}
     */
    public function __resolve()
    {
    }
}