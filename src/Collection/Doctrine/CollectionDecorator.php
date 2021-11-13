<?php

namespace Zenstruck\Collection\Doctrine;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection as DoctrineCollection;
use Zenstruck\Collection;
use Zenstruck\Collection\Paginatable;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class CollectionDecorator implements Collection, DoctrineCollection
{
    use Paginatable;

    private DoctrineCollection $inner;

    /**
     * @param iterable|DoctrineCollection|null $source
     */
    public function __construct($source = [])
    {
        $source = $source ?? [];

        if (!\is_iterable($source)) {
            throw new \InvalidArgumentException(); // todo
        }

        if (!$source instanceof DoctrineCollection) {
            $source = new ArrayCollection(\is_array($source) ? $source : \iterator_to_array($source));
        }

        $this->inner = $source;
    }

    public function take(int $limit, int $offset = 0): self
    {
        return new self($this->slice($offset, $limit));
    }

    public function count(): int
    {
        return $this->inner->count();
    }

    public function getIterator(): \Traversable
    {
        return $this->inner->getIterator();
    }

    /**
     * @return true
     */
    public function add($element): bool
    {
        return $this->inner->add($element);
    }

    public function clear(): void
    {
        $this->inner->clear();
    }

    public function contains($element): bool
    {
        return $this->inner->contains($element);
    }

    public function isEmpty(): bool
    {
        return $this->inner->isEmpty();
    }

    public function remove($key)
    {
        return $this->inner->remove($key);
    }

    public function removeElement($element): bool
    {
        return $this->inner->removeElement($element);
    }

    public function containsKey($key): bool
    {
        return $this->inner->containsKey($key);
    }

    public function get($key)
    {
        return $this->inner->get($key);
    }

    public function getKeys(): array
    {
        return $this->inner->getKeys();
    }

    public function getValues(): array
    {
        return $this->inner->getValues();
    }

    public function set($key, $value): void
    {
        $this->inner->set($key, $value);
    }

    public function toArray(): array
    {
        return $this->inner->toArray();
    }

    public function first()
    {
        return $this->inner->first();
    }

    public function last()
    {
        return $this->inner->last();
    }

    public function key()
    {
        return $this->inner->key();
    }

    public function current()
    {
        return $this->inner->current();
    }

    public function next()
    {
        return $this->inner->next();
    }

    public function exists(\Closure $p): bool
    {
        return $this->inner->exists($p);
    }

    public function filter(\Closure $p): self
    {
        return new self($this->inner->filter($p));
    }

    public function forAll(\Closure $p): bool
    {
        return $this->inner->forAll($p);
    }

    public function map(\Closure $func): self
    {
        return new self($this->inner->map($func));
    }

    public function partition(\Closure $p): array
    {
        return $this->inner->partition($p);
    }

    public function indexOf($element)
    {
        return $this->inner->indexOf($element);
    }

    public function slice($offset, $length = null): array
    {
        return $this->inner->slice($offset, $length);
    }

    public function offsetExists($offset): bool
    {
        return $this->inner->offsetExists($offset);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->inner->offsetGet($offset);
    }

    public function offsetSet($offset, $value): void
    {
        $this->inner->offsetSet($offset, $value);
    }

    public function offsetUnset($offset): void
    {
        $this->inner->offsetUnset($offset);
    }
}
