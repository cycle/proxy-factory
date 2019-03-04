<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\Promise\Declaration\Declaration;
use Cycle\ORM\PromiseFactoryInterface;
use Spiral\Core\Container;

class Factory implements PromiseFactoryInterface
{
    /** @var Container */
    private $container;

    /** @var ProxyPrinter */
    private $creator;

    public function __construct(Container $container, ProxyPrinter $creator)
    {
        $this->container = $container;
        $this->creator = $creator;
    }

    public function promise(ORMInterface $orm, string $role, array $scope): ?ReferenceInterface
    {
        $class = $orm->getSchema()->define($role, \Cycle\ORM\Schema::ENTITY);
        if (empty($class)) {
            return null;
        }

        $now = new \DateTime();
        $as = $class . 'Proxy_' . $now->format('Ymd_His_u');

        $schema = new Declaration($class, $as);
        $output = $this->creator->make($class, $as);
        $output = ltrim($output, "<?php");

        eval($output);

        return $this->makeProxyObject($orm, $role, $scope, $schema->class->getNamespacesName());
    }

    private function makeProxyObject(ORMInterface $orm, string $role, array $scope, string $className)
    {
        return $this->container->make($className, compact('orm', 'role', 'scope'));
    }
}