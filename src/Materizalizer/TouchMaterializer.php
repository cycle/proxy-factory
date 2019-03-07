<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Materizalizer;

use Cycle\ORM\Promise\Declaration\Declaration;
use Cycle\ORM\Promise\MaterializerInterface;

class TouchMaterializer implements MaterializerInterface
{
    /** @var string */
    private $directory;

    public function __construct(string $directory)
    {
        $this->directory = $directory;
    }

    /**
     * @todo add file changes check
     * {@inheritdoc}
     */
    public function materialize(string $code, Declaration $declaration): void
    {
        if (class_exists($declaration->class->getNamespacesName())) {
            throw new \RuntimeException("Class `{$declaration->class->getNamespacesName()}` already exists.");
        }

        $filename = $this->directory . DIRECTORY_SEPARATOR . $this->convertName($declaration);
        if (file_exists($filename)) {
            throw new \RuntimeException("Filename `$filename` already exists.");
        }

        file_put_contents($filename, $code);
    }

    private function convertName(Declaration $declaration): string
    {
        return str_replace('\\', '', $declaration->class->getNamespacesName());
    }
}