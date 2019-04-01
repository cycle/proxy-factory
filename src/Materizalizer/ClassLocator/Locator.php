<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Materizalizer\ClassLocator;

use Spiral\Tokenizer\ClassesInterface;
use Spiral\Tokenizer\ClassLocator;

class Locator implements ClassesInterface
{
    /** @var ClassLocator */
    private $locator;

    public function __construct(ClassLocator $locator)
    {
        $this->locator = $locator;
    }

    /**
     * {@inheritdoc}
     * @param string|null $interfaceToBeImplemented Additional check for targets to check interface implementation.
     *
     * @return \ReflectionClass[]
     */
    public function getClasses($target = null, string $interfaceToBeImplemented = null): array
    {
        /** @var \ReflectionClass[] $targets */
        $targets = $this->locator->getClasses($target);
        if (!empty($interfaceToBeImplemented)) {
            foreach ($targets as $name => $reflection) {
                if (!$reflection->implementsInterface($interfaceToBeImplemented)) {
                    unset($targets[$name]);
                }
            }
        }

        return $targets;
    }
}