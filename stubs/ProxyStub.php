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

    public function __isset($name)
    {
    }

    public function __unset($name)
    {
        if (in_array($name, $this->unsetProperties, true)) {
            $entity = $this->resolver->__resolve();

            unset($entity->{$name});
        } else {
            unset($this->{$name});
        }
    }

    public function __set($name, $value)
    {
        $this->resolver->__resolve()->{$name} = $value;
    }

    public function __get($name)
    {
        return $this->resolver->__resolve()->{$name};
    }

    public function __clone()
    {
        $this->resolver = clone $this->resolver;
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