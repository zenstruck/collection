<?php

namespace Zenstruck\Collection;

use Zenstruck\Collection;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class IterableCollection implements Collection
{
    use Paginatable;

    /** @var callable|iterable */
    private $source;

    /**
     * @param iterable|callable|null $source
     */
    public function __construct($source = null)
    {
        $source = $source ?? [];

        if (!\is_callable($source) && !\is_iterable($source)) {
            throw new \InvalidArgumentException('$source must be callable, iterable or null.');
        }

        if ($source instanceof \Generator) {
            throw new \InvalidArgumentException('$source must not be a generator directly as generators cannot be rewound. Try wrapping in a closure.');
        }

        $this->source = $source;
    }

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
