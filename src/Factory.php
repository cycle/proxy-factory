<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\Promise\Declaration\Declaration;
use Cycle\ORM\Promise\Naming\HashPostfixNaming;
use Cycle\ORM\PromiseFactoryInterface;
use Cycle\ORM\Schema;
use Spiral\Core\Container\SingletonInterface;

class Factory implements PromiseFactoryInterface, SingletonInterface
{
    /** @var ProxyPrinter */
    private $printer;

    /** @var MaterializerInterface */
    private $materializer;

    /** @var NamingInterface */
    private $naming;

    /** @var array */
    private $resolved = [];

    public function __construct(ProxyPrinter $printer, MaterializerInterface $materializer, ?NamingInterface $naming)
    {
        $this->printer = $printer;
        $this->materializer = $materializer;
        $this->naming = $naming ?? new HashPostfixNaming();
    }

    /**
     * @param ORMInterface $orm
     * @param string       $role
     * @param array        $scope
     *
     * @return ReferenceInterface|null
     * @throws ProxyFactoryException
     */
    public function promise(ORMInterface $orm, string $role, array $scope): ?ReferenceInterface
    {
        if (isset($this->resolved[$role])) {
            return $this->instantiate($this->resolved[$role], $orm, $role, $scope);
        }

        $class = $orm->getSchema()->define($role, Schema::ENTITY);
        if (empty($class)) {
            return null;
        }

        try {
            $r = new \ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new ProxyFactoryException($e->getMessage(), $e->getCode(), $e);
        }

        // ---

        $declaration = new Declaration($r, $this->naming->name($r));
        $this->materializer->materialize($this->printer->make($declaration), $declaration);

        // ---

        $this->resolved[$role] = $declaration->class->getFullName();

        return $this->instantiate($this->resolved[$role], $orm, $role, $scope);
    }

    /**
     * @param string                  $className
     * @param \Cycle\ORM\ORMInterface $orm
     * @param string                  $role
     * @param array                   $scope
     *
     * @return \Cycle\ORM\Promise\PromiseInterface
     */
    private function instantiate(string $className, ORMInterface $orm, string $role, array $scope): PromiseInterface
    {
        return new $className($orm, $role, $scope);
    }
}