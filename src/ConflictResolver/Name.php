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

    /**
     * Name constructor.
     */
    protected function __construct()
    {
    }

    /**
     * @param string $name
     * @param int    $sequence
     * @return Name
     */
    public static function createWithSequence(string $name, int $sequence): Name
    {
        $self = new self();
        $self->name = $name;
        $self->sequence = $sequence;

        return $self;
    }

    /**
     * @param string $name
     * @return Name
     */
    public static function create(string $name): Name
    {
        $self = new self();
        $self->name = $name;

        return $self;
    }

    /**
     * @param string|null $delimiter
     * @return string
     */
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
}
