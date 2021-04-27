<?php

namespace Zenstruck\Collection;

use Zenstruck\Collection;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class PageCollection implements \IteratorAggregate, \Countable
{
    private Collection $collection;
    private int $limit;
    private ?Page $page1;

    public function __construct(Collection $collection, int $limit = Page::DEFAULT_LIMIT)
    {
        $this->collection = $collection;
        $this->limit = $limit;
    }

    public function get(int $page): Page
    {
        return 1 === $page ? $this->page1() : new Page($this->collection, $page, $this->limit);
    }

    /**
     * @return Page[]|\Traversable
     */
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

    private function page1(): Page
    {
        return $this->page1 ??= new Page($this->collection, 1, $this->limit);
    }
}
