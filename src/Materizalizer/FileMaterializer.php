<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Materizalizer;

use Cycle\ORM\Promise\Declaration\Declaration;
use Cycle\ORM\Promise\MaterializerInterface;
use Cycle\ORM\Promise\Materizalizer\ClassLocator\Locator;
use Cycle\ORM\Promise\Materizalizer\ClassLocator\LocatorFactory;
use Cycle\ORM\Promise\PromiseInterface;

class FileMaterializer implements MaterializerInterface
{
    /** @var Locator */
    private $locator;

    /** @var string */
    private $directory;

    public function __construct(LocatorFactory $factory, string $directory)
    {
        $this->locator = $factory->create($directory);
        $this->directory = $directory;
    }

    /**
     * {@inheritdoc}
     */
    public function materialize(string $code, Declaration $declaration): void
    {
        if (class_exists($declaration->class->getFullName())) {
            return;
        }

        $targets = $this->locator->getClasses($declaration->parent->getFullName(), PromiseInterface::class);
        if (count($targets) === 0) {
            $this->create($code, $declaration);
        } else {
            $alreadyExists = false;
            foreach ($targets as $target) {
                if (!$this->isContentIdentical($target, $code, $declaration)) {
                    unlink($target->getFileName());
                } else {
                    $alreadyExists = true;
                }
            }

            if (!$alreadyExists) {
                $this->create($code, $declaration);
            }
        }
    }

    private function create(string $code, Declaration $declaration): void
    {
        file_put_contents($this->makeFilename($declaration), $code);
    }

    private function makeFilename(Declaration $declaration): string
    {
        return $this->directory . DIRECTORY_SEPARATOR . $this->convertName($declaration);
    }

    private function convertName(Declaration $declaration): string
    {
        return str_replace('\\', '', $declaration->class->getFullName());
    }

    private function isContentIdentical(\ReflectionClass $target, string $code, Declaration $declaration): bool
    {
        return $this->prepareCode($code, $declaration) === $this->prepareCode(file_get_contents($target->getFileName()), $declaration);
    }

    private function prepareCode(string $code, Declaration $declaration): string
    {
        $regexp = sprintf('"/class\s(\S+)\sextends\s%s/', $declaration->parent->getShortName());

        return preg_replace($regexp, '', $code);
    }
}