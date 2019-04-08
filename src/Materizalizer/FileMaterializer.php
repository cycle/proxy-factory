<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Materizalizer;

use Cycle\ORM\Promise\Declaration\Declaration;
use Cycle\ORM\Promise\MaterializerInterface;
use Spiral\Core\Container\SingletonInterface;

class FileMaterializer implements MaterializerInterface, SingletonInterface
{
    /** @var ModificationInspector */
    private $inspector;

    /** @var string */
    private $directory;

    /** @var array */
    private $materialized = [];

    public function __construct(ModificationInspector $inspector, string $directory)
    {
        $this->inspector = $inspector;
        $this->directory = $directory;
    }

    /**
     * {@inheritdoc}
     */
    public function materialize(string $code, Declaration $declaration, \ReflectionClass $reflection): void
    {
        if (class_exists($declaration->class->getFullName())) {
            dump("{$declaration->class->getFullName()} exists.");

            return;
        }

        $modifiedDate = $this->inspector->getLastModifiedDate($reflection);
        $filename = $this->makeFilename($declaration);

        if (!isset($this->materialized[$filename]) || $this->materialized[$filename] < $modifiedDate) {

            dump("{$declaration->class->getFullName()} materialized.\n\n");
            $this->materialized[$filename] = $modifiedDate;
            $this->create($filename, $code);
        }
    }

    private function makeFilename(Declaration $declaration): string
    {
        return $this->directory . DIRECTORY_SEPARATOR . $this->convertName($declaration) . '.php';
    }

    private function convertName(Declaration $declaration): string
    {
        return str_replace('\\', '', $declaration->class->getFullName());
    }

    private function create(string $filename, string $code): void
    {
        file_put_contents($filename, $code);
    }
}