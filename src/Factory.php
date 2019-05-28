<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\Promise\Declaration\Declarations;
use Cycle\ORM\Promise\Declaration\Extractor;
use Cycle\ORM\PromiseFactoryInterface;
use Cycle\ORM\Schema;
use Spiral\Core\Container\SingletonInterface;
use Doctrine\Instantiator\Instantiator;

final class Factory implements PromiseFactoryInterface, SingletonInterface
{
    /** @var ProxyPrinter */
    private $printer;

    /** @var MaterializerInterface */
    private $materializer;

    /** @var Names */
    private $names;

    /** @var Instantiator */
    private $instantiator;

    /** @var Extractor */
    private $extractor;

    /** @var array */
    private $resolved = [];

    public function __construct(
        ProxyPrinter $printer,
        MaterializerInterface $materializer,
        Names $names,
        Instantiator $instantiator,
        Extractor $extractor
    ) {
        $this->printer = $printer;
        $this->materializer = $materializer;
        $this->names = $names;
        $this->instantiator = $instantiator;
        $this->extractor = $extractor;
    }

    /**
     * @param ORMInterface $orm
     * @param string       $role
     * @param array        $scope
     *
     * @return ReferenceInterface|null
     * @throws ProxyFactoryException
     * @throws \Doctrine\Instantiator\Exception\ExceptionInterface
     */
    public function promise(ORMInterface $orm, string $role, array $scope): ?ReferenceInterface
    {
        $class = $orm->getSchema()->define($role, Schema::ENTITY);
        if (empty($class)) {
            return null;
        }

        try {
            $reflection = new \ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw ProxyFactoryException::wrap($e);
        }

        if (isset($this->resolved[$role])) {
            return $this->instantiate($reflection, $this->resolved[$role], $orm, $role, $scope);
        }

        $parent = Declarations::createParentFromReflection($reflection);
        $class = Declarations::createClassFromName($this->names->make($reflection), $parent);
        if (!class_exists($class->getFullName())) {
            $this->materializer->materialize($this->printer->make($reflection, $class, $parent), $class->getShortName(), $reflection);
        }

        $this->resolved[$role] = $class->getFullName();

        return $this->instantiate($reflection, $this->resolved[$role], $orm, $role, $scope);
    }

    /**
     * @param \ReflectionClass $reflection
     * @param string           $className
     * @param ORMInterface     $orm
     * @param string           $role
     * @param array            $scope
     *
     * @return PromiseInterface
     * @throws \Doctrine\Instantiator\Exception\ExceptionInterface
     */
    private function instantiate(\ReflectionClass $reflection, string $className, ORMInterface $orm, string $role, array $scope): PromiseInterface
    {
        $structure = $this->extractor->extract($reflection);

        /** @var PromiseInterface $instance */
        $instance = $this->instantiator->instantiate($className);
        $instance->{$this->printer->initMethodName($structure)}($orm, $role, $scope);

        return $instance;
    }
}