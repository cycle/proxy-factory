<?php
declare(strict_types=1);

namespace Spiral\Cycle\Promise;

use Spiral\Cycle\Mapper\MapperInterface;
use Spiral\Cycle\Promise\PromiseInterface;
use Spiral\Cycle\Promise\ProxyFactoryInterface;

class Factory implements ProxyFactoryInterface
{
    public function __construct()
    {
    }

    public function makeProxy(array $scope): ?PromiseInterface
    {

    }
}