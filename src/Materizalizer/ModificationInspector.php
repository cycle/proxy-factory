<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Materizalizer;

class ModificationInspector
{
    public function getLastModifiedDate(\ReflectionClass $reflection): \DateTime
    {
        $modifiedDate = $this->getLatestParentsModifiedDate($reflection);

        foreach ($reflection->getTraits() as $trait) {
            $traitModifiedDate = $this->getLatestParentsModifiedDate($trait);

            if ($traitModifiedDate > $modifiedDate) {
                $modifiedDate = $traitModifiedDate;
            }
        }

        foreach ($reflection->getInterfaces() as $interface) {
            $interfaceModifiedDate = $this->getLatestParentsModifiedDate($interface);

            if ($interfaceModifiedDate > $modifiedDate) {
                $modifiedDate = $interfaceModifiedDate;
            }
        }

        return $modifiedDate;
    }

    private function getLatestParentsModifiedDate(\ReflectionClass $reflection): \DateTime
    {
        $modifiedDate = new \DateTime('@' . filemtime($reflection->getFileName()));

        $parent = $reflection->getParentClass();
        while ($parent !== false) {
            $parentsModifiedDate = new \DateTime('@' . filemtime($parent->getFileName()));

            if ($parentsModifiedDate > $modifiedDate) {
                $modifiedDate = $parentsModifiedDate;
            }

            $parent = $parent->getParentClass();
        }

        return $modifiedDate;
    }
}