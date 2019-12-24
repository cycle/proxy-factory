<?php

/**
 * Spiral Framework. Cycle ProxyFactory
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */

declare(strict_types=1);

namespace Cycle\ORM\Promise\Declaration;

use Cycle\ORM\Promise\Declaration\Extractor\Constants;
use Cycle\ORM\Promise\Declaration\Extractor\Methods;
use Cycle\ORM\Promise\Declaration\Extractor\Properties;
use ReflectionClass;
use ReflectionException;

final class Extractor
{
    /** @var Extractor\Methods */
    private $methods;

    /** @var Extractor\Properties */
    private $properties;

    /** @var Extractor\Constants */
    private $constants;

    /**
     * @param Constants  $constants
     * @param Properties $properties
     * @param Methods    $methods
     */
    public function __construct(
        Extractor\Constants $constants,
        Extractor\Properties $properties,
        Extractor\Methods $methods
    ) {
        $this->constants = $constants;
        $this->properties = $properties;
        $this->methods = $methods;
    }

    /**
     * @param ReflectionClass $reflection
     * @return Structure
     * @throws ReflectionException
     */
    public function extract(ReflectionClass $reflection): Structure
    {
        return Structure::create(
            $this->constants->getConstants($reflection),
            $this->properties->getProperties($reflection),
            $this->hasCloneMethod($reflection),
            ...$this->methods->getMethods($reflection)
        );
    }

    /**
     * @param ReflectionClass $reflection
     * @return bool
     */
    private function hasCloneMethod(ReflectionClass $reflection): bool
    {
        if (!$reflection->hasMethod('__clone')) {
            return false;
        }

        try {
            $cloneMethod = $reflection->getMethod('__clone');
        } catch (ReflectionException $exception) {
            return false;
        }

        return !$cloneMethod->isPrivate();
    }
}
