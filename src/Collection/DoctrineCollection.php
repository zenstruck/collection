<?php

namespace Zenstruck\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection as Inner;
use Zenstruck\Collection;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template K of array-key
 * @template V
 * @implements Collection<K,V>
 * @implements Inner<K,V>
 */
final class DoctrineCollection implements Collection, Inner
{
    /** @use IterableCollection<K,V> */
    use IterableCollection {
        map as private innerMap;
    }

    /** @var Inner<K,V> */
    private Inner $inner;

    /**
     * @param iterable<K,V>|Inner<K,V>|null $source
     */
    public function __construct(iterable|Inner|null $source = [])
    {
        $source ??= [];

        if (!$source instanceof Inner) {
            $source = new ArrayCollection(\is_array($source) ? $source : \iterator_to_array($source));
        }

        $this->inner = $source;
    }

    /**
     * @return self<K,V>
     */
    public function take(int $limit, int $offset = 0): self
    {
        return new self($this->slice($offset, $limit));
    }

    public function count(): int
    {
        return $this->inner->count();
    }

    /**
     * @return \Traversable<K,V>
     */
    public function getIterator(): \Traversable
    {
        return $this->inner->getIterator();
    }

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

    public function remove($key): mixed
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

    public function get($key): mixed
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

    public function first(mixed $default = null): mixed
    {
        return false === ($result = $this->inner->first()) ? $default : $result;
    }

    public function last(): mixed
    {
        return $this->inner->last();
    }

    public function key(): int|string|null
    {
        return $this->inner->key();
    }

    public function current(): mixed
    {
        return $this->inner->current();
    }

    public function next(): mixed
    {
        return $this->inner->next();
    }

    public function exists(\Closure $p): bool
    {
        return $this->inner->exists($p);
    }

    /**
     * @return self<K,V>
     */
    public function filter(\Closure|callable $p): self
    {
        return new self($this->inner->filter(\Closure::fromCallable($p)));
    }

    /**
     * @param callable(V,K):bool $predicate
     *
     * @return self<K,V>
     */
    public function reject(callable $predicate): self
    {
        return $this->filter(fn($value, $key) => !$predicate($value, $key));
    }

    public function forAll(\Closure $p): bool
    {
        return $this->inner->forAll($p);
    }

    /**
     * @template T
     *
     * @param \Closure|callable(V,K):T $func
     *
     * @return self<K,T>
     */
    public function map(\Closure|callable $func): self
    {
        return new self($this->innerMap($func));
    }

    public function partition(\Closure $p): array
    {
        return $this->inner->partition($p);
    }

    public function indexOf($element): int|string|bool
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

    public function offsetGet($offset): mixed
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
