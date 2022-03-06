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
    /** @use ExtraMethods<K,V> */
    use ExtraMethods;

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
