<?php

namespace Zenstruck\Collection\Doctrine\ORM;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template T of object
 *
 * @mixin T
 */
final class EntityWithAggregates
{
    /** @var T */
    private object $entity;

    /** @var mixed[] */
    private array $aggregates;

    /**
     * @param T       $entity
     * @param mixed[] $aggregates
     */
    public function __construct(object $entity, array $aggregates)
    {
        $this->entity = $entity;
        $this->aggregates = $aggregates;
    }

    /**
     * @param mixed[] $arguments
     */
    public function __call(string $name, array $arguments): mixed
    {
        return $this->entity->{$name}(...$arguments);
    }

    public function __get(string $name): mixed
    {
        return $this->aggregates[$name] ?? $this->entity->{$name};
    }

    public function __set(string $name, mixed $value): void
    {
        $this->entity->{$name} = $value;
    }

    public function __isset(string $name): bool
    {
        return isset($this->aggregates[$name]) || isset($this->entity->{$name});
    }

    public function __unset(string $name): void
    {
        unset($this->entity->{$name});
    }

    /**
     * @return T
     */
    public function entity(): object
    {
        return $this->entity;
    }

    /**
     * @return mixed[]
     */
    public function aggregates(): array
    {
        return $this->aggregates;
    }
}
