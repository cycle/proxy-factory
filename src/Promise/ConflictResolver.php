<?php

/**
 * Spiral Framework. Cycle ProxyFactory
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */
declare(strict_types=1);

namespace Cycle\ORM\Promise;

use Cycle\ORM\Promise\ConflictResolver\Sequences;

final class ConflictResolver
{
    /** @var ConflictResolver\Sequences */
    private $sequences;

    /**
     * @param Sequences|null $sequences
     */
    public function __construct(ConflictResolver\Sequences $sequences = null)
    {
        $this->sequences = $sequences ?? new Sequences();
    }

    /**
     * @param array  $names
     * @param string $name
     * @return ConflictResolver\Name
     */
    public function resolve(array $names, string $name): ConflictResolver\Name
    {
        return $this->addPostfix($this->initiateCounters($names), $this->parseName($name));
    }

    /**
     * @param array                 $counters
     * @param ConflictResolver\Name $name
     * @return ConflictResolver\Name
     */
    private function addPostfix(array $counters, ConflictResolver\Name $name): ConflictResolver\Name
    {
        if (isset($counters[$name->name])) {
            $sequence = $this->sequences->find(array_keys($counters[$name->name]), $name->sequence);
            if ($sequence !== $name->sequence) {
                $name->sequence = $sequence;
            }
        }

        return $name;
    }

    /**
     * @param array $names
     * @return array
     */
    private function initiateCounters(array $names): array
    {
        $counters = [];
        foreach ($names as $name) {
            $name = $this->parseName($name);

            if (isset($counters[$name->name])) {
                $counters[$name->name][$name->sequence] = $name->fullName();
            } else {
                $counters[$name->name] = [$name->sequence => $name->fullName()];
            }
        }

        return $counters;
    }

    /**
     * @param string $name
     * @return ConflictResolver\Name
     */
    private function parseName(string $name): ConflictResolver\Name
    {
        if (preg_match("/\d+$/", $name, $match)) {
            $sequence = (int)$match[0];
            if ($sequence > 0) {
                return ConflictResolver\Name::createWithSequence(
                    trimTrailingDigits($name, $sequence),
                    $sequence
                );
            }
        }

        return ConflictResolver\Name::create($name);
    }
}
