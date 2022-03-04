<?php

namespace Zenstruck\Collection;

/**
 * Adds extra useful collection methods that are "lazy".
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template K of array-key
 * @template V
 */
trait ExtraMethods
{
    /** @use Paginatable<V> */
    use Paginatable;

    /**
     * @param callable(V,K):bool $func
     *
     * @return IterableCollection<K,V>
     */
    public function filter(callable $func): IterableCollection
    {
        return new IterableCollection(function() use ($func) {
            foreach ($this as $key => $value) {
                if ($func($value, $key)) {
                    yield $key => $value;
                }
            }
        });
    }

    /**
     * Opposite of {@see filter()}.
     *
     * @param callable(V,K):bool $func
     *
     * @return IterableCollection<K,V>
     */
    public function reject(callable $func): IterableCollection
    {
        return $this->filter(fn($value, $key) => !$func($value, $key));
    }

    /**
     * @template T of array-key|\Stringable
     *
     * @param callable(V,K):T $func
     *
     * @return IterableCollection<array-key,V>
     */
    public function keyBy(callable $func): IterableCollection
    {
        return new IterableCollection(function() use ($func) {
            foreach ($this as $key => $value) {
                $key = $func($value, $key);

                yield $key instanceof \Stringable ? (string) $key : $key => $value;
            }
        });
    }

    /**
     * @template T
     *
     * @param callable(V,K):T $func
     *
     * @return IterableCollection<K,T>
     */
    public function map(callable $func): IterableCollection
    {
        return new IterableCollection(function() use ($func) {
            foreach ($this as $key => $value) {
                yield $key => $func($value, $key);
            }
        });
    }

    /**
     * @template T of array-key|\Stringable
     * @template U
     *
     * @param callable(V,K):iterable<T,U> $func
     *
     * @return IterableCollection<array-key,U>
     */
    public function mapWithKeys(callable $func): IterableCollection
    {
        return new IterableCollection(function() use ($func) {
            foreach ($this as $key => $value) {
                foreach ($func($value, $key) as $newKey => $newValue) {
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
     * @param callable(V,K):bool $filter
     * @param D                  $default
     *
     * @return V|D
     */
    public function firstWhere(callable $filter, mixed $default = null): mixed
    {
        foreach ($this as $key => $value) {
            if ($filter($value, $key)) {
                return $value;
            }
        }

        return $default;
    }

    public function isEmpty(): bool
    {
        return 0 === $this->count();
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
