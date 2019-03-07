<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\Promise\Declaration\Declaration;
use Cycle\ORM\Promise\Naming\DatetimeNaming;
use Cycle\ORM\PromiseFactoryInterface;
use Cycle\ORM\Schema;

class Factory implements PromiseFactoryInterface
{
    /** @var ProxyPrinter */
    private $creator;

    /** @var MaterializerInterface */
    private $materializer;

    /** @var NamingInterface */
    private $naming;

    public function __construct(ProxyPrinter $creator, MaterializerInterface $materializer, ?NamingInterface $naming)
    {
        $this->creator = $creator;
        $this->materializer = $materializer;
        $this->naming = $naming ?? new DatetimeNaming();
    }

    public function promise(ORMInterface $orm, string $role, array $scope): ?ReferenceInterface
    {
        $class = $orm->getSchema()->define($role, Schema::ENTITY);
        if (empty($class)) {
            return null;
        }

        $declaration = new Declaration($class, $this->naming->name($class));
        $output = $this->creator->make($declaration);

        $this->materializer->materialize($output, $declaration);

        return $this->instantiate($orm, $role, $scope, $declaration->class->getNamespacesName());
    }

    private function instantiate(ORMInterface $orm, string $role, array $scope, string $className): PromiseInterface
    {
        return new $className($orm, $role, $scope);
    }
}