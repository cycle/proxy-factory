<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Declaration;

use Cycle\ORM\Promise\Traverser;

class Extractor
{
    /** @var Traverser */
    private $traverser;

    public function __construct(Traverser $traverser)
    {
        $this->traverser = $traverser;
    }

    public function extract(string $class): Structure
    {
        $class = new \ReflectionClass($class);

        $properties = new Visitor\LocateProperties();
        $methods = new Visitor\LocateMethodsToBeProxied();

        $this->traverser->traverseFilename($class->getFileName(), $properties, $methods);

        return Structure::create($properties->getProperties(), $methods->getMethods(), $class->getConstructor() !== null);
    }
}