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
final class LazyCollection implements Collection
{
    /** @use IterableCollection<K,V> */
    use IterableCollection {
        take as private traitTake;
    }

    /** @var iterable<K,V>|\Closure():iterable<K,V> */
    private \Closure|iterable $source;

    /**
     * @param null|iterable<K,V>|callable():iterable<K,V> $source
     */
    public function __construct(iterable|callable|null $source = null)
    {
        $source ??= [];

        if ($source instanceof \Generator) {
            throw new \InvalidArgumentException('$source must not be a generator directly as generators cannot be rewound. Try wrapping in a closure.');
        }

        if (\is_callable($source) && (!\is_iterable($source) || \is_array($source))) {
            $source = $source instanceof \Closure ? $source : \Closure::fromCallable($source);
        }

        $this->source = $source;
    }

    /**
     * @return self<K,V>
     */
    public function take(int $limit, int $offset = 0): self
    {
        if (\is_array($source = &$this->normalizeSource())) {
            return new self(\array_slice($source, $offset, $limit, true));
        }

        return $this->traitTake($limit, $offset);
    }

    /**
     * @return array<K,V>
     */
    public function toArray(): array
    {
        if (\is_array($source = &$this->normalizeSource())) {
            return $source;
        }

        return \iterator_to_array($source);
    }

    public function getIterator(): \Traversable
    {
        $source = &$this->normalizeSource();

        foreach ($source as $key => $value) {
            yield $key => $value;
        }
    }

    public function count(): int
    {
        if (\is_countable($source = &$this->normalizeSource())) {
            return \count($source);
        }

        return \iterator_count($source);
    }

    /**
     * @return iterable<K,V>
     */
    private function &normalizeSource(): iterable
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

        $this->source = $source;

        return $this->source;
    }
}
