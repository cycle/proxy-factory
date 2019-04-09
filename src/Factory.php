<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\Promise\Declaration\Declaration;
use Cycle\ORM\PromiseFactoryInterface;
use Cycle\ORM\Schema;
use Spiral\Core\Container\SingletonInterface;

final class Factory implements PromiseFactoryInterface, SingletonInterface
{
    /** @var ProxyPrinter */
    private $printer;

    /** @var MaterializerInterface */
    private $materializer;

    /** @var array */
    private $resolved = [];

    /** @var Names */
    private $names;

    public function __construct(ProxyPrinter $printer, MaterializerInterface $materializer, Names $names)
    {
        $this->printer = $printer;
        $this->materializer = $materializer;
        $this->names = $names;
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
            $reflection = new \ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw ProxyFactoryException::wrap($e);
        }

        $declaration = Declaration::createFromReflection($reflection, $this->names->make($reflection));
        if (!class_exists($declaration->class->getFullName())) {
            $this->materializer->materialize($this->printer->make($reflection, $declaration), $declaration->class->getShortName(), $reflection);
        }

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