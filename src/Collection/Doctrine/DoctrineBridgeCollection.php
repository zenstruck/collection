<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Doctrine;

use Doctrine\Common\Collections\AbstractLazyCollection;
use Doctrine\Common\Collections\ArrayCollection as DoctrineArrayCollection;
use Doctrine\Common\Collections\Collection as DoctrineCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Zenstruck\Collection;
use Zenstruck\Collection\ArrayCollection;
use Zenstruck\Collection\Doctrine\Specification\CriteriaInterpreter;
use Zenstruck\Collection\IterableCollection;
use Zenstruck\Collection\Matchable;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template K of array-key
 * @template V
 * @implements Collection<K,V>
 * @implements DoctrineCollection<K,V>
 * @implements Matchable<K,V>
 */
final class DoctrineBridgeCollection implements Collection, DoctrineCollection, Matchable
{
    /** @use IterableCollection<K,V> */
    use IterableCollection {
        map as private innerMap;
        reduce as private innerReduce;
        find as private innerFind;
    }

    /** @var DoctrineCollection<K,V> */
    private DoctrineCollection $inner;

    /**
     * @param iterable<K,V>|DoctrineCollection<K,V>|null $source
     */
    public function __construct(iterable|DoctrineCollection|null $source = [])
    {
        if (null === $source) {
            $source = [];
        }

        if (!$source instanceof DoctrineCollection) {
            $source = new DoctrineArrayCollection(\is_array($source) ? $source : \iterator_to_array($source));
        }

        $this->inner = $source;
    }

    public function first(mixed $default = null): mixed
    {
        if ($this->inner instanceof AbstractLazyCollection && !$this->inner->isInitialized()) {
            return $this->slice(0, 1)[0] ?? $default;
        }

        return $this->inner->first() ?? $default; // @phpstan-ignore-line
    }

    public function findFirst(\Closure $p): mixed
    {
        if (\method_exists($this->inner, 'findFirst')) {
            return $this->inner->findFirst($p);
        }

        throw new \LogicException(\sprintf('Method "%s::findFirst()" not available. Try upgrading to doctrine/collections 2.0+.', $this->inner::class));
    }

    /**
     * @param Criteria|callable(V,K):bool $specification
     */
    public function find(mixed $specification, mixed $default = null): mixed
    {
        if ($specification instanceof Criteria) {
            return $this->filter($specification->setMaxResults(1))->first($default);
        }

        if (!\is_callable($specification)) {
            return $this->find(CriteriaInterpreter::interpret($specification, self::class, 'find'), $default);
        }

        return $this->innerFind($specification, $default);
    }

    /**
     * @param Criteria|callable(V,K):bool $specification
     *
     * @return self<K,V>
     */
    public function filter(mixed $specification): self
    {
        if ($this->inner instanceof Selectable && $specification instanceof Criteria) {
            return new self($this->inner->matching($specification));
        }

        if ($this->inner instanceof Criteria) {
            throw new \LogicException(\sprintf('"%s" is not an instance of "%s". Cannot use Criteria as a specification.', $this->inner::class, Selectable::class));
        }

        if (!\is_callable($specification)) {
            return $this->filter(CriteriaInterpreter::interpret($specification, self::class, 'filter'));
        }

        return new self($this->inner->filter($specification(...)));
    }

    public function reduce(\Closure|callable $function, mixed $initial = null): mixed
    {
        return $this->innerReduce($function, $initial);
    }

    /**
     * @return self<K,V>
     */
    public function map(\Closure|callable $function): self
    {
        return new self($this->innerMap($function)); // @phpstan-ignore-line
    }

    /**
     * @return self<K,V>
     */
    public function take(int $limit, int $offset = 0): self
    {
        return new self($this->slice($offset, $limit));
    }

    public function isEmpty(): bool
    {
        return $this->inner->isEmpty();
    }

    public function eager(): ArrayCollection
    {
        return new ArrayCollection($this->inner->toArray());
    }

    public function add($element): void
    {
        $this->inner->add($element);
    }

    public function clear(): void
    {
        $this->inner->clear();
    }

    public function remove($key): mixed
    {
        return $this->inner->remove($key);
    }

    public function removeElement($element): bool
    {
        return $this->inner->removeElement($element);
    }

    public function set($key, $value): void
    {
        $this->inner->set($key, $value);
    }

    public function partition(\Closure $p): array
    {
        return $this->inner->partition($p);
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->inner->offsetExists($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->inner->offsetGet($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->inner->offsetSet($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->inner->offsetUnset($offset);
    }

    public function count(): int
    {
        return $this->inner->count();
    }

    public function contains($element): bool
    {
        return $this->inner->contains($element);
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

    public function toArray(): array
    {
        return $this->inner->toArray();
    }

    public function last(): mixed
    {
        return $this->inner->last();
    }

    public function key(): mixed
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

    public function slice($offset, $length = null): array
    {
        return $this->inner->slice($offset, $length);
    }

    public function exists(\Closure $p): bool
    {
        return $this->inner->exists($p);
    }

    public function forAll(\Closure $p): bool
    {
        return $this->inner->forAll($p);
    }

    public function indexOf($element): mixed
    {
        return $this->inner->indexOf($element);
    }

    public function getIterator(): \Traversable
    {
        if ($this->inner instanceof AbstractLazyCollection && !$this->inner->isInitialized()) {
            foreach ($this->pages() as $page) {
                yield from $page;
            }

            return;
        }

        yield from $this->inner->getIterator();
    }
}
