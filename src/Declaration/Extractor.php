<?php
/**
 * Spiral Framework.
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */
declare(strict_types=1);

namespace Cycle\ORM\Promise\Declaration;

use Cycle\ORM\Promise\Declaration\Extractor\Constants;
use Cycle\ORM\Promise\Declaration\Extractor\Methods;
use Cycle\ORM\Promise\Declaration\Extractor\Properties;

final class Extractor
{
    /** @var Extractor\Methods */
    private $methods;

    /** @var Extractor\Properties */
    private $properties;

    /** @var Extractor\Constants */
    private $constants;

    /**
     * @param Constants|null  $constants
     * @param Properties|null $properties
     * @param Methods|null    $methods
     */
    public function __construct(
        Extractor\Constants $constants = null,
        Extractor\Properties $properties = null,
        Extractor\Methods $methods = null
    ) {
        $this->constants = $constants ?? new Constants();
        $this->properties = $properties ?? new Properties();
        $this->methods = $methods ?? new Methods();
    }

    /**
     * @param \ReflectionClass $reflection
     * @return Structure
     */
    public function extract(\ReflectionClass $reflection): Structure
    {
        return Structure::create(
            $this->constants->getConstants($reflection),
            $this->properties->getProperties($reflection),
            $this->methods->getMethods($reflection),
            $this->hasCloneMethod($reflection)
        );
    }

    /**
     * @param \ReflectionClass $reflection
     * @return bool
     */
    private function hasCloneMethod(\ReflectionClass $reflection): bool
    {
        if (!$reflection->hasMethod('__clone')) {
            return false;
        }

        try {
            $cloneMethod = $reflection->getMethod('__clone');
        } catch (\ReflectionException $exception) {
            return false;
        }

        return !$cloneMethod->isPrivate();
    }
}