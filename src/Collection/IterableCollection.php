<?php

namespace Zenstruck\Collection;

use Zenstruck\Collection;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template K of array-key
 * @template V
 * @implements Collection<K,V>
 */
final class IterableCollection implements Collection
{
    /** @use Paginatable<V> */
    use Paginatable;

    /** @var \Closure():iterable<K,V>|iterable<K,V> */
    private \Closure|iterable $source;

    /**
     * @param iterable<K,V>|callable():iterable<K,V>|null $source
     */
    public function __construct(iterable|callable|null $source = null)
    {
        $source ??= [];

        if ($source instanceof \Generator) {
            throw new \InvalidArgumentException('$source must not be a generator directly as generators cannot be rewound. Try wrapping in a closure.');
        }

        if (\is_callable($source) && (!\is_iterable($source) || \is_array($source))) {
            $source = \Closure::fromCallable($source);
        }

        $this->source = $source;
    }

    /**
     * @return self<K,V>
     */
    public function take(int $limit, int $offset = 0): self
    {
        if (\is_array($source = $this->normalizeSource())) {
            return new self(\array_slice($source, $offset, $limit, true));
        }

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
     * @return array<K,V>
     */
    public function toArray(): array
    {
        if (\is_array($source = $this->normalizeSource())) {
            return $source;
        }

        return \iterator_to_array($source);
    }

    /**
     * @param callable(V,K):bool $func
     *
     * @return self<K,V>
     */
    public function filter(callable $func): self
    {
        return new self(function() use ($func) {
            foreach ($this->normalizeSource() as $key => $value) {
                if ($func($value, $key)) {
                    yield $key => $value;
                }
            }
        });
    }

    /**
     * @param callable(V,K):bool $func
     *
     * @return self<K,V>
     */
    public function reject(callable $func): self
    {
        return $this->filter(fn($value, $key) => !$func($value, $key));
    }

    /**
     * @template T of array-key
     *
     * @param callable(V,K):T $func
     *
     * @return self<T,V>
     */
    public function keyBy(callable $func): self
    {
        return new self(function() use ($func) {
            foreach ($this->normalizeSource() as $key => $value) {
                yield $func($value, $key) => $value;
            }
        });
    }

    /**
     * @template T
     *
     * @param callable(V,K):T $func
     *
     * @return self<K,T>
     */
    public function map(callable $func): self
    {
        return new self(function() use ($func) {
            foreach ($this->normalizeSource() as $key => $value) {
                yield $key => $func($value, $key);
            }
        });
    }

    /**
     * @template T of array-key
     * @template U
     *
     * @param callable(V,K):iterable<T,U> $func
     *
     * @return self<T,U>
     */
    public function mapWithKeys(callable $func): self
    {
        return new self(function() use ($func) {
            foreach ($this->normalizeSource() as $key => $value) {
                foreach ($func($value, $key) as $newKey => $newValue) {
                    yield $newKey => $newValue;

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

    /**
     * @return ArrayCollection<K,V>
     */
    public function eager(): ArrayCollection
    {
        return new ArrayCollection($this->toArray());
    }

    /**
     * @return self<K,V>
     */
    public function dump(): self
    {
        \function_exists('dump') ? dump($this->toArray()) : \var_dump($this->toArray());

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

    public function getIterator(): \Traversable
    {
        foreach ($this->normalizeSource() as $key => $value) {
            yield $key => $value;
        }
    }

    public function count(): int
    {
        if (\is_countable($source = $this->normalizeSource())) {
            return \count($source);
        }

        return \iterator_count($source);
    }

    /**
     * @return iterable<K,V>
     */
    private function normalizeSource(): iterable
    {
        if (\is_iterable($this->source)) {
            return $this->source;
        }

        // source is callback
        $source = ($this->source)();

        if ($source instanceof \Generator) {
            // generators cannot be rewound so don't set as $source (ensure callback is executed next time)
            return $source;
        }

        if (!\is_iterable($source)) {
            throw new \InvalidArgumentException('$source callback must return iterable.');
        }

        return $this->source = $source;
    }
}
