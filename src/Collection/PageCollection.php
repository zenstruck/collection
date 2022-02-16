<?php

namespace Zenstruck\Collection;

use Zenstruck\Collection;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template Value
 * @implements \IteratorAggregate<int,Page<Value>>
 */
final class PageCollection implements \IteratorAggregate, \Countable
{
    /** @var Collection<int,Value> */
    private Collection $collection;
    private int $limit;

    /** @var Page<Value> */
    private Page $page1;

    /**
     * @param Collection<int,Value> $collection
     */
    public function __construct(Collection $collection, int $limit = Page::DEFAULT_LIMIT)
    {
        $this->collection = $collection;
        $this->limit = $limit;
    }

    /**
     * @return Page<Value>
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

        yield $this->page1();

        for ($page = 2; $page <= $this->count(); ++$page) {
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
     * @return Page<Value>
     */
    private function page1(): Page
    {
        return $this->page1 ??= new Page($this->collection, 1, $this->limit);
    }
}
