<?php
/**
 * Spiral Framework.
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */
declare(strict_types=1);

namespace Cycle\ORM\Promise;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\Promise\Declaration\Declaration\NullDeclaration;
use Cycle\ORM\Promise\Declaration\Declarations;
use Cycle\ORM\Promise\Declaration\Extractor;
use Cycle\ORM\Promise\Declaration\Structure;
use Cycle\ORM\Promise\Printers;
use Cycle\ORM\PromiseFactoryInterface;
use Doctrine\Instantiator\Instantiator;
use Spiral\Core\Container\SingletonInterface;

final class Factory implements PromiseFactoryInterface, SingletonInterface
{
    /** @var Printers\ProxyPrinter */
    private $printer;

    /** @var Printers\NullPromisePrinter */
    private $nullEntityPrinter;

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

    /** @var Schema */
    private $schema;

    public function __construct(
        Printers\ProxyPrinter $printer,
        Printers\NullPromisePrinter $nullEntityPrinter,
        MaterializerInterface $materializer,
        Names $names,
        Instantiator $instantiator,
        Extractor $extractor,
        Schema $schema
    ) {
        $this->printer = $printer;
        $this->nullEntityPrinter = $nullEntityPrinter;
        $this->materializer = $materializer;
        $this->names = $names;
        $this->instantiator = $instantiator;
        $this->extractor = $extractor;
        $this->schema = $schema;
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
        $class = $orm->getSchema()->define($role, \Cycle\ORM\Schema::ENTITY);
        if (!empty($class)) {
            if (isset($this->resolved[$role])) {
                return $this->instantiateNull($this->resolved[$role], $orm, $role, $scope);
            }

            $name = '\Cycle\ORM\Promise\NullPromise';
            $class = Declarations::createClass($name);
            if (!class_exists($class->getFullName())) {
                $structure = Structure::create([], [], [], false);
                print_r($this->nullEntityPrinter->make($structure, $class, new NullDeclaration()));
                $this->materializer->materialize($this->nullEntityPrinter->make($structure, $class, new NullDeclaration()), null, null);
            }

            $this->resolved[$role] = $class->getFullName();

            return $this->instantiateNull($this->resolved[$role], $orm, $role, $scope);
        }

        try {
            $reflection = new \ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw ProxyFactoryException::wrap($e);
        }

        $structure = $this->extractor->extract($reflection);
        if (isset($this->resolved[$role])) {
            return $this->instantiate($structure, $this->resolved[$role], $orm, $role, $scope);
        }

        $parent = Declarations::createParentFromReflection($reflection);
        $class = Declarations::createClassFromName($this->names->make($reflection), $parent);
        if (!class_exists($class->getFullName())) {
            $this->materializer->materialize($this->printer->make($structure, $class, $parent), $class->getShortName(), $reflection);
        }

        $this->resolved[$role] = $class->getFullName();

        return $this->instantiate($structure, $this->resolved[$role], $orm, $role, $scope);
    }

    /**
     * @param Structure    $structure
     * @param string       $className
     * @param ORMInterface $orm
     * @param string       $role
     * @param array        $scope
     * @return PromiseInterface
     *
     * @throws \Doctrine\Instantiator\Exception\ExceptionInterface
     */
    private function instantiate(?Structure $structure, string $className, ORMInterface $orm, string $role, array $scope): PromiseInterface
    {
        /** @var PromiseInterface $instance */
        $instance = $this->instantiator->instantiate($className);
        $instance->{$this->schema->initMethodName($structure)}($orm, $role, $scope);

        return $instance;
    }

    /**
     * @param string       $className
     * @param ORMInterface $orm
     * @param string       $role
     * @param array        $scope
     * @return PromiseInterface
     *
     * @throws \Doctrine\Instantiator\Exception\ExceptionInterface
     */
    private function instantiateNull(string $className, ORMInterface $orm, string $role, array $scope): PromiseInterface
    {
        return $this->instantiate(null, $className, $orm, $role, $scope);
    }
}