<?php
declare(strict_types=1);

namespace Spiral\Cycle\Promise;

use Spiral\Cycle\Tests\Fixtures\PromiseResolver;

trait ResolverTrait
{
    abstract protected function __resolver(): PromiseResolver;

    public function __loaded(): bool
    {
        return $this->__resolver()->__loaded();
    }

    public function __role(): string
    {
        return $this->__resolver()->__role();
    }

    public function __scope(): array
    {
        return $this->__resolver()->__scope();
    }

    public function __resolve()
    {
        return $this->__resolver()->__resolve();
    }
}