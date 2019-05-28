<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Example;

use Cycle\ORM\Promise\PromiseInterface;

class ProxyStub implements PromiseInterface
{
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