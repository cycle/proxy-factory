<?php
declare(strict_types=1);

namespace Spiral\Cycle\Promise\Declaration;

use Spiral\Cycle\Promise\Traverser;

class Extractor
{
    /** @var Traverser */
    private $traverser;

    public function __construct(Traverser $traverser)
    {
        $this->traverser = $traverser;
    }

    public function extract(string $filename): Declaration
    {
        $properties = new Visitor\LocateProperties();
        $methods = new Visitor\LocateMethods();

        $this->traverser->traverseFilename($filename, $properties, $methods);

        return Declaration::create($properties->getProperties(), $methods->getMethods(), $methods->hasConstructor());
    }
}