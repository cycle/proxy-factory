<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Materizalizer;

use Cycle\ORM\Promise\MaterializerInterface;
use Cycle\ORM\Promise\Utils;
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

    private function makeFilename(string $className): string
    {
        return $this->directory . DIRECTORY_SEPARATOR . $className . '.php';
    }

    private function prepareCode(string $code): string
    {
        return "<?php\n" . trim(Utils::trimPHPOpenTag($code));
    }

    private function create(string $filename, string $code): void
    {
        file_put_contents($filename, $code);
    }
}