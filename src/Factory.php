<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\Promise\Declaration\Declaration;
use Cycle\ORM\PromiseFactoryInterface;
use Cycle\ORM\Schema;
use Spiral\Core\Container\SingletonInterface;

class Factory implements PromiseFactoryInterface, SingletonInterface
{
    /** @var ProxyPrinter */
    private $printer;

    /** @var MaterializerInterface */
    private $materializer;

    /** @var array */
    private $resolved = [];

    public function __construct(ProxyPrinter $printer, MaterializerInterface $materializer)
    {
        $this->printer = $printer;
        $this->materializer = $materializer;
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

        $declaration = new Declaration($r, $this->createName($r));
        $this->materializer->materialize($this->printer->make($declaration), $declaration);

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

    private function createName(\ReflectionClass $reflection): string
    {
        return "{$reflection->getShortName()}_{$reflection->getName()}{$reflection->getFileName()}";
    }
}