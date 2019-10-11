<?php

/**
 * Spiral Framework. Cycle ProxyFactory
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */
declare(strict_types=1);

namespace Cycle\ORM\Promise\Materizalizer;

use Cycle\ORM\Promise\MaterializerInterface;
use Spiral\Core\Container\SingletonInterface;

use function Cycle\ORM\Promise\trimPHPOpenTag;

final class FileMaterializer implements MaterializerInterface, SingletonInterface
{
    /** @var ModificationInspector */
    private $inspector;

    /** @var string */
    private $directory;

    /** @var array */
    private $materialized = [];

    /**
     * @param ModificationInspector $inspector
     * @param string                $directory
     */
    public function __construct(ModificationInspector $inspector, string $directory)
    {
        $this->inspector = $inspector;
        $this->directory = $directory;
    }

    /**
     * {@inheritdoc}
     */
    public function materialize(string $code, string $shortClassName, \ReflectionClass $reflection): void
    {
        $modifiedDate = $this->inspector->getLastModifiedDate($reflection);
        $filename = $this->makeFilename($shortClassName);

        if (!isset($this->materialized[$filename]) || $this->materialized[$filename] < $modifiedDate) {
            $this->materialized[$filename] = $modifiedDate;
            $this->create($filename, $this->prepareCode($code));

            require_once($filename);
        }
    }

    /**
     * @param string $className
     * @return string
     */
    private function makeFilename(string $className): string
    {
        return $this->directory . DIRECTORY_SEPARATOR . $className . '.php';
    }

    /**
     * @param string $code
     * @return string
     */
    private function prepareCode(string $code): string
    {
        return "<?php\n" . trim(trimPHPOpenTag($code));
    }

    /**
     * @param string $filename
     * @param string $code
     */
    private function create(string $filename, string $code): void
    {
        file_put_contents($filename, $code);
    }
}
