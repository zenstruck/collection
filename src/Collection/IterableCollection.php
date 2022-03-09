<?php

namespace Zenstruck\Collection;

/**
 * Convert any {@see \Traversable} into a {@see Collection} with
 * extra, "lazy" methods.
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template K of array-key
 * @template V
 */
trait IterableCollection
{
    /** @use Paginatable<V> */
    use Paginatable;

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
            return new self();
        }

        return new self(function() use ($limit, $offset) {
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
     * @param callable(V,K):bool $predicate
     *
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
     * @template T of array-key|\Stringable
     *
     * @param callable(V,K):T $function
     *
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
     * @template D
     *
     * @param D $default
     *
     * @return V|D
     */
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
