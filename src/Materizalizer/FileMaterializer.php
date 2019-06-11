<?php
/**
 * Spiral Framework.
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */
declare(strict_types=1);

namespace Cycle\ORM\Promise\Materizalizer;

use Cycle\ORM\Promise\MaterializerInterface;
use Cycle\ORM\Promise\Utils;
use Spiral\Core\Container\SingletonInterface;

final class FileMaterializer implements MaterializerInterface, SingletonInterface
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
    public function materialize(string $code, ?string $shortClassName, ?\ReflectionClass $reflection): void
    {
        if ($shortClassName === null) {
            throw new \LogicException('Class name should not be empty.');
        }

        if ($reflection === null) {
            throw new \LogicException('Reflection Class should not be empty.');
        }

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