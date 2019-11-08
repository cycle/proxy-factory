<?php

/**
 * Spiral Framework. Cycle ProxyFactory
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */

declare(strict_types=1);

namespace Cycle\ORM\Promise;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\Promise\Declaration\Declarations;
use Cycle\ORM\Promise\Declaration\Extractor;
use Cycle\ORM\Promise\Exception\ProxyFactoryException;
use Cycle\ORM\Promise\Materizalizer\EvalMaterializer;
use Cycle\ORM\PromiseFactoryInterface;
use Cycle\ORM\Schema;
use Doctrine\Instantiator\Instantiator;
use Spiral\Core\Container\SingletonInterface;

final class ProxyFactory implements PromiseFactoryInterface, SingletonInterface
{
    /** @var Extractor */
    private $extractor;

    /** @var Printer */
    private $printer;

    /** @var MaterializerInterface */
    private $materializer;

    /** @var Names */
    private $names;

    /** @var Instantiator */
    private $instantiator;


    /** @var array */
    private $resolved = [];

    /**
     * @param Extractor                  $extractor
     * @param Printer                    $printer
     * @param Instantiator               $instantiator
     * @param MaterializerInterface|null $materializer
     * @param Names|null                 $names
     */
    public function __construct(
        Extractor $extractor,
        Printer $printer,
        Instantiator $instantiator = null,
        MaterializerInterface $materializer = null,
        Names $names = null
    ) {
        $this->extractor = $extractor;
        $this->printer = $printer;
        $this->instantiator = $instantiator;
        $this->materializer = $materializer ?? new EvalMaterializer();
        $this->names = $names ?? new Names();
    }

    /**
     * @param ORMInterface $orm
     * @param string       $role
     * @param array        $scope
     * @return PromiseInterface
     *
     * @throws ProxyFactoryException
     * @throws \Doctrine\Instantiator\Exception\ExceptionInterface
     */
    public function promise(ORMInterface $orm, string $role, array $scope): PromiseInterface
    {
        $class = $orm->getSchema()->define($role, Schema::ENTITY);
        if (empty($class)) {
            return new PromiseOne($orm, $role, $scope);
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
            $this->materializer->materialize(
                $this->printer->make($reflection, $class, $parent),
                $class->getShortName(),
                $reflection
            );
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
     * @return PromiseInterface
     *
     * @throws \Doctrine\Instantiator\Exception\ExceptionInterface
     */
    private function instantiate(
        \ReflectionClass $reflection,
        string $className,
        ORMInterface $orm,
        string $role,
        array $scope
    ): PromiseInterface {
        $structure = $this->extractor->extract($reflection);

        /** @var PromiseInterface $instance */
        $instance = $this->instantiator->instantiate($className);
        $instance->{$this->printer->initMethodName($structure)}($orm, $role, $scope);

        return $instance;
    }
}
