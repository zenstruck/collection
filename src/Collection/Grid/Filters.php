<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Grid;

use Zenstruck\Collection\ArrayCollection;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @implements \IteratorAggregate<string,Filter>
 */
final class Filters implements \IteratorAggregate, \Countable
{
    /** @var ArrayCollection<string,Filter> */
    private ArrayCollection $filters;

    /**
     * @param array<string,Filter> $filters
     */
    public function __construct(array $filters)
    {
        $this->filters = new ArrayCollection($filters);
    }

    public function get(string $name): ?Filter
    {
        return $this->filters->get($name);
    }

    public function has(string $name): bool
    {
        return $this->filters->has($name);
    }

    /**
     * @return ArrayCollection<string,Filter>
     */
    public function all(): ArrayCollection
    {
        return $this->filters;
    }

    public function getIterator(): \Traversable
    {
        return $this->filters;
    }

    public function count(): int
    {
        return $this->filters->count();
    }
}
