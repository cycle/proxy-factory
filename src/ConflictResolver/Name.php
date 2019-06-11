<?php
/**
 * Spiral Framework.
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */
declare(strict_types=1);

namespace Cycle\ORM\Promise\ConflictResolver;

final class Name
{
    /** @var string */
    public $name;

    /** @var int */
    public $sequence = 0;

    public static function createWithSequence(string $name, int $sequence): Name
    {
        $self = new self();
        $self->name = $name;
        $self->sequence = $sequence;

        return $self;
    }

    public static function create(string $name): Name
    {
        $self = new self();
        $self->name = $name;

        return $self;
    }

    public function fullName(string $delimiter = null): string
    {
        $name = $this->name;
        if ($this->sequence > 0) {
            if ($delimiter !== null) {
                return $name . $delimiter . $this->sequence;
            }

            return $name . $this->sequence;
        }

        return $name;
    }

    protected function __construct()
    {
    }
}