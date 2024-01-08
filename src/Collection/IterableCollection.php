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
 * Convert any {@see \Traversable} class into a {@see Collection}
 * with some extra, "lazy" methods.
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template K
 * @template V
 */
trait IterableCollection
{
    /**
     * @return LazyCollection<K,V>
     */
    public function take(int $limit, int $offset = 0): Collection
    {
        $source = $this->iterableSource();

        if ($source instanceof \ArrayIterator || $source instanceof \ArrayObject) {
            return new LazyCollection(\array_slice($source->getArrayCopy(), $offset, $limit, true));
        }

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
    public function filter(mixed $specification): Collection
    {
        if (!\is_callable($specification)) {
            throw InvalidSpecification::build($specification, static::class, 'filter', 'Only callable(V,K):bool is supported.');
        }

        return new LazyCollection(function() use ($specification) {
            foreach ($this as $key => $value) {
                if ($specification($value, $key)) {
                    yield $key => $value;
                }
            }
        });
    }

    /**
     * @return LazyCollection<K,V>
     */
    public function keyBy(callable $function): Collection
    {
        return new LazyCollection(function() use ($function) {
            foreach ($this as $key => $value) {
                yield $function($value, $key) => $value;
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
    public function map(callable $function): Collection
    {
        return new LazyCollection(function() use ($function) {
            foreach ($this as $key => $value) {
                yield $key => $function($value, $key);
            }
        });
    }

    /**
     * @return Page<K,V>
     */
    public function paginate(int $page = 1, int $limit = Page::DEFAULT_LIMIT): Page
    {
        return $this->pages($limit)->get($page);
    }

    /**
     * @return Pages<K,V>
     */
    public function pages(int $limit = Page::DEFAULT_LIMIT): Pages
    {
        return new Pages($this, $limit);
    }

    public function first(mixed $default = null): mixed
    {
        foreach ($this as $value) {
            return $value;
        }

        return $default;
    }

    public function find(mixed $specification, mixed $default = null): mixed
    {
        if (!\is_callable($specification)) {
            throw InvalidSpecification::build($specification, static::class, 'find', 'Only callable(V,K):bool is supported.');
        }

        foreach ($this as $key => $value) {
            if ($specification($value, $key)) {
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

    public function isEmpty(): bool
    {
        return 0 === $this->count();
    }

    public function count(): int
    {
        $source = $this->iterableSource();

        return \is_countable($source) ? \count($source) : \iterator_count($source);
    }

    public function getIterator(): \Traversable
    {
        foreach ($this->iterableSource() as $key => $value) {
            yield $key => $value;
        }
    }

    public function dump(): static
    {
        \function_exists('dump') ? dump(\iterator_to_array($this)) : \var_dump(\iterator_to_array($this));

        return $this;
    }

    public function dd(): void
    {
        $this->dump();

        exit;
    }

    public function eager(): ArrayCollection
    {
        return new ArrayCollection($this->iterableSource());
    }

    /**
     * @return iterable<K,V>
     */
    private function iterableSource(): iterable
    {
        return $this;
    }
}
