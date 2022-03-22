<?php

namespace Zenstruck\Collection;

/**
 * Convert any {@see \Traversable} class into a {@see Collection}
 * with some extra, "lazy" methods.
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template K of array-key
 * @template V
 */
trait IterableCollection
{
    /**
     * @return LazyCollection<K,V>
     */
    public function take(int $limit, int $offset = 0): LazyCollection
    {
        if ($limit < 0) {
            throw new \InvalidArgumentException('$limit cannot be negative');
        }

        if ($offset < 0) {
            throw new \InvalidArgumentException('$offset cannot be negative');
        }

        if (0 === $limit) {
            return new LazyCollection();
        }

        return new LazyCollection(function() use ($limit, $offset) {
            $i = 0;

            foreach ($this as $key => $value) {
                if ($i++ < $offset) {
                    continue;
                }

                yield $key => $value;

                if ($i >= $offset + $limit) {
                    break;
                }
            }
        });
    }

    /**
     * @return LazyCollection<K,V>
     */
    public function filter(callable $predicate): LazyCollection
    {
        return new LazyCollection(function() use ($predicate) {
            foreach ($this as $key => $value) {
                if ($predicate($value, $key)) {
                    yield $key => $value;
                }
            }
        });
    }

    /**
     * Opposite of {@see filter()}.
     *
     * @param callable(V,K):bool $predicate
     *
     * @return LazyCollection<K,V>
     */
    public function reject(callable $predicate): LazyCollection
    {
        return $this->filter(fn($value, $key) => !$predicate($value, $key));
    }

    /**
     * @return LazyCollection<array-key,V>
     */
    public function keyBy(callable $function): LazyCollection
    {
        return new LazyCollection(function() use ($function) {
            foreach ($this as $key => $value) {
                $key = $function($value, $key);

                yield $key instanceof \Stringable ? (string) $key : $key => $value;
            }
        });
    }

    /**
     * @template T
     *
     * @param callable(V,K):T $function
     *
     * @return LazyCollection<K,T>
     */
    public function map(callable $function): LazyCollection
    {
        return new LazyCollection(function() use ($function) {
            foreach ($this as $key => $value) {
                yield $key => $function($value, $key);
            }
        });
    }

    /**
     * @template T of array-key|\Stringable
     * @template U
     *
     * @param callable(V,K):iterable<T,U> $function
     *
     * @return LazyCollection<array-key,U>
     */
    public function mapWithKeys(callable $function): LazyCollection
    {
        return new LazyCollection(function() use ($function) {
            foreach ($this as $key => $value) {
                foreach ($function($value, $key) as $newKey => $newValue) {
                    yield $newKey instanceof \Stringable ? (string) $newKey : $newKey => $newValue;

                    continue 2;
                }
            }
        });
    }

    /**
     * @return Page<V>
     */
    public function paginate(int $page = 1, int $limit = Page::DEFAULT_LIMIT): Page
    {
        return $this->pages($limit)->get($page);
    }

    /**
     * @return PageCollection<V>
     */
    public function pages(int $limit = Page::DEFAULT_LIMIT): PageCollection
    {
        return new PageCollection($this, $limit);
    }

    public function first(mixed $default = null): mixed
    {
        foreach ($this as $value) {
            return $value;
        }

        return $default;
    }

    /**
     * @template D
     *
     * @param callable(V,K):bool $predicate
     * @param D                  $default
     *
     * @return V|D
     */
    public function firstWhere(callable $predicate, mixed $default = null): mixed
    {
        foreach ($this as $key => $value) {
            if ($predicate($value, $key)) {
                return $value;
            }
        }

        return $default;
    }

    public function reduce(callable $function, mixed $initial = null): mixed
    {
        $result = $initial;

        foreach ($this as $key => $value) {
            $result = $function($result, $value, $key);
        }

        return $result;
    }

    public function sum(callable $selector): int|float
    {
        return $this->reduce(fn($result, $value, $key) => $result + $selector($value, $key), 0);
    }

    public function isEmpty(): bool
    {
        return 0 === $this->count();
    }

    public function count(): int
    {
        return \iterator_count($this);
    }

    public function dump(): static
    {
        \function_exists('dump') ? dump(\iterator_to_array($this)) : \var_dump(\iterator_to_array($this));

        return $this;
    }

    /**
     * @return never
     */
    public function dd(): void
    {
        $this->dump();

        exit;
    }

    /**
     * @return ArrayCollection<K,V>
     */
    public function eager(): ArrayCollection
    {
        return new ArrayCollection($this);
    }
}
