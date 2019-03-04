<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\Promise\Declaration\Declaration;
use Cycle\ORM\Promise\Materizalizer\EvalMaterializer;
use Cycle\ORM\Promise\Naming\DatetimeNaming;
use Cycle\ORM\PromiseFactoryInterface;
use Cycle\ORM\Schema;
use Spiral\Core\Container;

class Factory implements PromiseFactoryInterface
{
    /** @var Container */
    private $container;

    /** @var ProxyPrinter */
    private $creator;

    /** @var NamingInterface */
    private $naming;

    /** @var MaterializerInterface */
    private $materializer;

    public function __construct(Container $container, ProxyPrinter $creator, ?NamingInterface $naming, MaterializerInterface $materializer)
    {
        $this->container = $container;
        $this->creator = $creator;
        $this->naming = $naming ?? new DatetimeNaming();
        $this->materializer = $materializer ?? new EvalMaterializer();
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

        return $this->makeProxyObject($orm, $role, $scope, $declaration->class->getNamespacesName());
    }

    private function makeProxyObject(ORMInterface $orm, string $role, array $scope, string $className)
    {
        return $this->container->make($className, compact('orm', 'role', 'scope'));
    }
}