<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\PromiseFactoryInterface;

class Factory implements PromiseFactoryInterface
{
    public function __construct()
    {
    }

    public function promise(ORMInterface $orm, string $role, array $scope): ?ReferenceInterface
    {
        // TODO: Implement promise() method.
    }
}