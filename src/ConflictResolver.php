<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise;

final class ConflictResolver
{
    /** @var ConflictResolver\Sequences */
    private $sequences;

    public function __construct(ConflictResolver\Sequences $sequences)
    {
        $this->sequences = $sequences;
    }

    public function resolve(array $names, string $name): string
    {
        return $this->addPostfix($this->initiateCounters($names), $this->parseName($name));
    }

    private function addPostfix(array $counters, ConflictResolver\Name $name): string
    {
        if (isset($counters[$name->name])) {
            $sequence = $this->sequences->find(array_keys($counters[$name->name]), $name->sequence);
            if ($sequence !== $name->sequence) {
                $name->sequence = $sequence;
            }
        }

        return $name->fullName();
    }

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

    private function parseName(string $name): ConflictResolver\Name
    {
        if (preg_match("/\d+$/", $name, $match)) {
            $sequence = (int)$match[0];
            if ($sequence > 0) {
                return ConflictResolver\Name::createWithSequence(Utils::trimTrailingDigits($name, $sequence), $sequence);
            }
        }

        return ConflictResolver\Name::create($name);
    }
}