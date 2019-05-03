<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests\Fixtures;

/**
 * @entity
 */
class SchematicEntity
{
    /**
     * @column(type=primary)
     * @var int
     */
    protected $id;

    /**
     * @column(type=string)
     * @var string
     */
    protected $name;

    /**
     * @column(type=string)
     * @var string
     */
    public $email;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}