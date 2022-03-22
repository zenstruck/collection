<?php

namespace Zenstruck\Collection;

use Zenstruck\Collection;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template K of array-key
 * @template V
 * @implements Collection<K,V>
 * @implements \ArrayAccess<K,V>
 */
final class ArrayCollection implements Collection, \ArrayAccess
{
    /** @use IterableCollection<K,V> */
    use IterableCollection {
        eager as private;
        sum as private traitSum;
    }

    /** @var array<K,V> */
    private array $source;

    /**
     * @param null|iterable<K,V>|callable():iterable<K,V> $source
     */
    public function __construct(iterable|callable|null $source = null)
    {
        if ($source instanceof \Traversable) {
            $source = \iterator_to_array($source);
        }

        $this->source = \is_array($source) ? $source : (new LazyCollection($source))->toArray();
    }

    /**
     * @param null|iterable<K,V>|callable():iterable<K,V> $source
     *
     * @return self<K,V>
     */
    public static function for(iterable|callable|null $source = null): self
    {
        return new self($source);
    }

    /**
     * @return self<array-key,mixed>
     */
    public static function wrap(mixed $value): self
    {
        if (null === $value) {
            $value = [];
        }

        return new self(\is_iterable($value) ? $value : [$value]);
    }

    /**
     * Create instance using {@see explode()}.
     *
     * Normalizes empty result into empty array: [''] => [].
     *
     * @param non-empty-string $separator
     *
     * @return self<int,string>
     */
    public static function explode(string $separator, string $string, ?int $limit = null): self
    {
        $exploded = null === $limit ? \explode($separator, $string) : \explode($separator, $string, $limit);

        return new self($exploded === [''] ? [] : $exploded);
    }

    /**
     * Create instance using {@see range()}.
     *
     * @template T of int|string|float
     *
     * @param T $start
     * @param T $end
     *
     * @return self<int,T>
     */
    public static function range(int|string|float $start, int|string|float $end, int|float $step = 1): self
    {
        return new self(\range($start, $end, $step));
    }

    /**
     * Create instance using {@see array_fill()}.
     *
     * @template T
     *
     * @param T $value
     *
     * @return self<int,T>
     */
    public static function fill(int $start, int $count, mixed $value): self
    {
        return new self(\array_fill($start, $count, $value));
    }

    /**
     * @return self<K,V>
     */
    public function take(int $limit, int $offset = 0): self
    {
        return $this->slice($offset, $limit);
    }

    /**
     * @return array<K,V>
     */
    public function all(): array
    {
        return $this->source;
    }

    /**
     * @return self<int,K>
     */
    public function keys(): self
    {
        return new self(\array_keys($this->source));
    }

    /**
     * @return self<int,V>
     */
    public function values(): self
    {
        return new self(\array_values($this->source));
    }

    /**
     * @return self<K,V>
     */
    public function reverse(): self
    {
        return new self(\array_reverse($this->source, true));
    }

    /**
     * @return self<K,V>
     */
    public function slice(int $offset, ?int $length = null): self
    {
        return new self(\array_slice($this->source, $offset, $length, true));
    }

    /**
     * @param iterable<K,V> ...$with
     *
     * @return self<K,V>
     */
    public function merge(iterable ...$with): self
    {
        return new self(
            \array_merge($this->source, ...\array_map(static fn(iterable $x) => self::for($x)->source, $with))
        );
    }

    /**
     * @param null|callable(V,K):bool $predicate
     *
     * @return self<K,V>
     */
    public function filter(?callable $predicate = null): self
    {
        return new self(\array_filter($this->source, $predicate, \ARRAY_FILTER_USE_BOTH));
    }

    /**
     * Opposite of {@see filter()}.
     *
     * @param null|callable(V,K):bool $predicate
     *
     * @return self<K,V>
     */
    public function reject(?callable $predicate = null): self
    {
        $predicate ??= static fn($value, $key) => (bool) $value;

        return $this->filter(fn($value, $key) => !$predicate($value, $key));
    }

    public function sum(?callable $selector = null): int|float
    {
        return $selector ? $this->traitSum($selector) : \array_sum($this->source);
    }

    /**
     * @template T of array-key|\Stringable
     *
     * @param callable(V,K):T $function
     *
     * @return self<array-key,V>
     */
    public function keyBy(callable $function): self
    {
        $results = [];

        foreach ($this->source as $key => $value) {
            $key = $function($value, $key);

            $results[$key instanceof \Stringable ? (string) $key : $key] = $value;
        }

        return new self($results);
    }

    /**
     * @template T
     *
     * @param callable(V,K):T $function
     *
     * @return self<K,T>
     */
    public function map(callable $function): self
    {
        $keys = \array_keys($this->source);

        return new self(\array_combine($keys, \array_map($function, $this->source, $keys)));
    }

    /**
     * @template T of array-key|\Stringable
     * @template U
     *
     * @param callable(V,K):iterable<T,U> $function
     *
     * @return self<array-key,U>
     */
    public function mapWithKeys(callable $function): self
    {
        $results = [];

        foreach ($this->source as $key => $value) {
            foreach ($function($value, $key) as $newKey => $newValue) {
                $results[$newKey instanceof \Stringable ? (string) $newKey : $newKey] = $newValue;

                continue 2;
            }
        }

        return new self($results);
    }

    /**
     * @return self<K,V>
     */
    public function sort(int|callable $flags = \SORT_REGULAR): self
    {
        $items = $this->source;
        \is_callable($flags) ? \uasort($items, $flags) : \asort($items, $flags);

        return new self($items);
    }

    /**
     * @return self<K,V>
     */
    public function sortDesc(int|callable $flags = \SORT_REGULAR): self
    {
        return $this->sort($flags)->reverse();
    }

    /**
     * @param callable(V,K):mixed $function
     *
     * @return self<K,V>
     */
    public function sortBy(callable $function, int $flags = \SORT_REGULAR): self
    {
        $results = [];

        // calculate comparator
        foreach ($this->source as $key => $value) {
            $results[$key] = $function($value, $key);
        }

        \asort($results, $flags);

        foreach (\array_keys($results) as $key) {
            $results[$key] = $this->source[$key];
        }

        return new self($results);
    }

    /**
     * @param callable(V,K):mixed $function
     *
     * @return self<K,V>
     */
    public function sortByDesc(callable $function, int $flags = \SORT_REGULAR): self
    {
        return $this->sortBy($function, $flags)->reverse();
    }

    /**
     * @return self<K,V>
     */
    public function sortKeys(int $flags = \SORT_REGULAR): self
    {
        $items = $this->source;

        \ksort($items, $flags);

        return new self($items);
    }

    /**
     * @return self<K,V>
     */
    public function sortKeysDesc(int $flags = \SORT_REGULAR): self
    {
        $items = $this->source;

        \krsort($items, $flags);

        return new self($items);
    }

    /**
     * @template T
     *
     * @param iterable<array-key,T> $values
     *
     * @return self<V&array-key,T>
     */
    public function combine(iterable $values): self
    {
        return new self(\array_combine($this->source, self::for($values)->source));
    }

    /**
     * @return self<V&array-key,V>
     */
    public function combineWithSelf(): self
    {
        return new self(\array_combine($this->source, $this->source));
    }

    /**
     * @template T of array-key|\Stringable
     *
     * @param callable(V,K):T $function
     *
     * @return self<array-key,non-empty-array<int,V>>
     */
    public function groupBy(callable $function): self
    {
        $results = [];

        foreach ($this->source as $key => $value) {
            $newKey = $function($value, $key);

            $results[$newKey instanceof \Stringable ? (string) $newKey : $newKey][] = $value;
        }

        return new self($results);
    }

    /**
     * @template D
     *
     * @param K $key
     * @param D $default
     *
     * @return V|D
     */
    public function get(int|string $key, mixed $default = null): mixed
    {
        return $this->keyExists($key) ? $this->source[$key] : $default;
    }

    /**
     * @param K $key
     * @param V $value
     *
     * @return self<K,V>
     */
    public function set(int|string $key, mixed $value): self
    {
        $this->source[$key] = $value;

        return $this;
    }

    /**
     * @param K ...$keys
     *
     * @return $this
     */
    public function unset(int|string ...$keys): self
    {
        foreach ($keys as $key) {
            unset($this->source[$key]);
        }

        return $this;
    }

    /**
     * @param K ...$keys
     *
     * @return $this
     */
    public function only(int|string ...$keys): self
    {
        $this->source = \array_intersect_key($this->source, \array_flip($keys));

        return $this;
    }

    /**
     * @param V ...$values
     *
     * @return $this
     */
    public function push(mixed ...$values): self
    {
        foreach ($values as $value) {
            $this->source[] = $value;
        }

        return $this;
    }

    /**
     * @param V $needle
     */
    public function in(mixed $needle): bool
    {
        return \in_array($needle, $this->source, true);
    }

    /**
     * @param K $key
     */
    public function keyExists(string|int $key): bool
    {
        return isset($this->source[$key]) || \array_key_exists($key, $this->source);
    }

    public function implode(string $separator = ''): string
    {
        return \implode($separator, $this->source);
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->source);
    }

    public function count(): int
    {
        return \count($this->source);
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->keyExists($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->source[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (isset($offset)) {
            $this->source[$offset] = $value;

            return;
        }

        $this->source[] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->source[$offset]);
    }
}
