<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection;

use Zenstruck\Collection;
use Zenstruck\Collection\Exception\InvalidSpecification;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @immutable
 *
 * @template V
 * @template K of array-key = array-key
 * @implements Collection<V,K>
 */
final class ArrayCollection implements Collection
{
    /** @use IterableCollection<V,K> */
    use IterableCollection;

    /** @var array<K,V> */
    private array $source;

    /**
     * @param null|iterable<K,V>|callable():iterable<K,V> $source
     */
    public function __construct(iterable|callable|null $source = null)
    {
        if (null === $source) {
            $source = [];
        }

        if (\is_callable($source) && !\is_iterable($source)) {
            $source = $source();
        }

        $this->source = $source instanceof \Traversable ? \iterator_to_array($source) : $source;
    }

    /**
     * @param null|iterable<K,V>|callable():iterable<K,V> $source
     *
     * @return self<V,K>
     */
    public static function for(iterable|callable|null $source = null): self
    {
        return new self($source);
    }

    /**
     * @return self<mixed>
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
     * @return self<string,int>
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
     * @return self<T,int>
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
     * @return self<T,int>
     */
    public static function fill(int $start, int $count, mixed $value): self
    {
        return new self(\array_fill($start, $count, $value));
    }

    public function first(mixed $default = null): mixed
    {
        return $this->source[\array_key_first($this->source) ?? ''] ?? $default;
    }

    /**
     * @return self<V,K>
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
     * @return self<K,int>
     */
    public function keys(): self
    {
        return new self(\array_keys($this->source));
    }

    /**
     * @return self<V,int>
     */
    public function values(): self
    {
        return new self(\array_values($this->source));
    }

    /**
     * @return self<V,K>
     */
    public function reverse(): self
    {
        return new self(\array_reverse($this->source, true));
    }

    /**
     * @return self<V,K>
     */
    public function slice(int $offset, ?int $length = null): self
    {
        return new self(\array_slice($this->source, $offset, $length, true));
    }

    /**
     * @param iterable<K,V> ...$with
     *
     * @return self<V,K>
     */
    public function merge(iterable ...$with): self
    {
        return new self(
            \array_merge($this->source, ...\array_map(static fn(iterable $x) => self::for($x)->source, $with)),
        );
    }

    /**
     * @param null|callable(V,K):bool $specification
     *
     * @return self<V,K>
     */
    public function filter(mixed $specification = null): self
    {
        if (null !== $specification && !\is_callable($specification)) {
            throw InvalidSpecification::build($specification, self::class, 'filter', 'Only null|callable(V,K):bool is supported.');
        }

        return new self(\array_filter($this->source, $specification, \ARRAY_FILTER_USE_BOTH));
    }

    /**
     * @template T of array-key|\Stringable
     *
     * @param callable(V,K):T $function
     *
     * @return self<V>
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
     * @return self<T,K>
     */
    public function map(callable $function): self
    {
        $keys = \array_keys($this->source);

        return new self(\array_combine($keys, \array_map($function, $this->source, $keys)));
    }

    /**
     * @return self<V,K>
     */
    public function sort(int|callable $flags = \SORT_REGULAR): self
    {
        $items = $this->source;
        \is_callable($flags) ? \uasort($items, $flags) : \asort($items, $flags);

        return new self($items);
    }

    /**
     * @return self<V,K>
     */
    public function sortDesc(int|callable $flags = \SORT_REGULAR): self
    {
        return $this->sort($flags)->reverse();
    }

    /**
     * @param callable(V,K):mixed $function
     *
     * @return self<V,K>
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
     * @return self<V,K>
     */
    public function sortByDesc(callable $function, int $flags = \SORT_REGULAR): self
    {
        return $this->sortBy($function, $flags)->reverse();
    }

    /**
     * @return self<V,K>
     */
    public function sortKeys(int $flags = \SORT_REGULAR): self
    {
        $items = $this->source;

        \ksort($items, $flags);

        return new self($items);
    }

    /**
     * @return self<V,K>
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
     * @return self<T,V&array-key>
     */
    public function combine(iterable $values): self
    {
        return new self(\array_combine($this->source, self::for($values)->source)); // @phpstan-ignore return.type, argument.type
    }

    /**
     * @return self<V,V&array-key>
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
     * @return self<non-empty-list<V>>
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
        return $this->has($key) ? $this->source[$key] : $default;
    }

    /**
     * @param K $key
     * @param V $value
     *
     * @return self<V,K>
     */
    public function set(int|string $key, mixed $value): self
    {
        $clone = clone $this;
        $clone->source[$key] = $value; // @phpstan-ignore property.readOnlyByPhpDocAssignNotInConstructor

        return $clone;
    }

    /**
     * @param K ...$keys
     *
     * @return self<V,K>
     */
    public function unset(int|string ...$keys): self
    {
        $clone = clone $this;

        foreach ($keys as $key) {
            unset($clone->source[$key]); // @phpstan-ignore property.readOnlyByPhpDocAssignNotInConstructor
        }

        return $clone;
    }

    /**
     * @param K ...$keys
     *
     * @return self<V,K>
     */
    public function only(int|string ...$keys): self
    {
        return new self(\array_intersect_key($this->source, \array_flip($keys)));
    }

    /**
     * @param V ...$values
     *
     * @return self<V,K>
     */
    public function push(mixed ...$values): self
    {
        $clone = clone $this;

        foreach ($values as $value) {
            $clone->source[] = $value; // @phpstan-ignore property.readOnlyByPhpDocAssignNotInConstructor
        }

        return $clone;
    }

    /**
     * @param V $needle
     */
    public function contains(mixed $needle): bool
    {
        return \in_array($needle, $this->source, true);
    }

    /**
     * @param K $key
     */
    public function has(string|int $key): bool
    {
        return \array_key_exists($key, $this->source);
    }

    public function implode(string $separator = ''): string
    {
        return \implode($separator, $this->source);
    }

    public function eager(): self
    {
        return $this;
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->source);
    }

    public function count(): int
    {
        return \count($this->source);
    }
}
