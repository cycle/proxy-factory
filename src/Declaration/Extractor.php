<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Declaration;

final class Extractor
{
    /** @var Extractor\Methods */
    private $methods;

    /** @var Extractor\Properties */
    private $properties;

    public function __construct(Extractor\Methods $methods, Extractor\Properties $properties)
    {
        $this->methods = $methods;
        $this->properties = $properties;
    }

    public function extract(string $reflection): Structure
    {
        $reflection = new \ReflectionClass($reflection);

        return Structure::create(
            $this->properties->getProperties($reflection),
            $this->methods->getMethods($reflection),
            $reflection->getConstructor() !== null
        );
    }
}