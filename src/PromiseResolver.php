<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\Select;

final class PromiseResolver implements PromiseInterface
{
    /** @var ORMInterface */
    private $orm;

    /** @var string */
    private $role;

    /** @var array */
    private $scope;

    /** @var bool */
    private $loaded;

    /** @var PromiseInterface|null */
    private $entity;

    /**
     * @param ORMInterface $orm
     * @param string       $role
     * @param array        $scope
     */
    public function __construct(ORMInterface $orm, string $role, array $scope)
    {
        $this->orm = $orm;
        $this->role = $role;
        $this->scope = $scope;
    }

    public function __clone()
    {
        if ($this->entity !== null) {
            $this->entity = clone $this->entity;
        }
    }

    /**
     * @inheritdoc
     */
    public function __loaded(): bool
    {
        return $this->loaded;
    }

    /**
     * @inheritdoc
     */
    public function __role(): string
    {
        return $this->role;
    }

    /**
     * @inheritdoc
     */
    public function __scope(): array
    {
        return $this->scope;
    }

    /**
     * @inheritdoc
     */
    public function __resolve()
    {
        if (!$this->loaded) {
            $this->loaded = true;

            // use entity from heap, if has already been loaded in memory otherwise select from repository
            $this->entity = $this->getEntityFromHeap() ?? $this->getEntityFromSource();
        }

        return $this->entity;
    }

    /**
     * @return object|null
     */
    private function getEntityFromHeap()
    {
        $key = key($this->scope);
        $value = $this->scope[$key];

        return $this->orm->getHeap()->find($this->role, $key, $value);
    }

    /**
     * @return object|null
     */
    private function getEntityFromSource()
    {
        $select = new Select($this->orm, $this->role);

        return $select->constrain(
            $this->orm->getSource($this->role)->getConstrain()
        )->fetchOne($this->scope);
    }
}