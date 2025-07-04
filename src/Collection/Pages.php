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

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template V
 * @template K = array-key
 * @implements \IteratorAggregate<int,Page<V,K>>
 */
final class Pages implements \IteratorAggregate, \Countable
{
    /** @var Page<V,K> */
    private Page $page1;

    /**
     * @param Collection<V,K> $collection
     * @param positive-int    $limit
     */
    public function __construct(private Collection $collection, private int $limit = Page::DEFAULT_LIMIT)
    {
    }

    /**
     * @param positive-int $page
     *
     * @return Page<V,K>
     */
    public function get(int $page): Page
    {
        return 1 === $page ? $this->page1() : new Page($this->collection, $page, $this->limit);
    }

    public function getIterator(): \Traversable
    {
        if (0 === $this->count()) {
            return;
        }

        for ($page = 1; $page <= $this->count(); ++$page) {
            yield $this->get($page);
        }
    }

    public function count(): int
    {
        if (0 === $this->page1()->count()) {
            return 0;
        }

        return $this->page1()->pageCount();
    }

    /**
     * @return Page<V,K>
     */
    private function page1(): Page
    {
        return $this->page1 ??= new Page($this->collection, 1, $this->limit);
    }
}
