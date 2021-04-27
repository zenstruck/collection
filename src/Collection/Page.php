<?php

namespace Zenstruck\Collection;

use Zenstruck\Collection;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Page implements \IteratorAggregate, \Countable
{
    public const DEFAULT_LIMIT = 20;

    private Collection $collection;
    private int $page;
    private int $limit;
    private ?Collection $cachedPage = null;

    public function __construct(Collection $collection, int $page = 1, int $limit = self::DEFAULT_LIMIT)
    {
        $this->collection = $collection;
        $this->page = $page < 1 ? 1 : $page;
        $this->limit = $limit < 1 ? self::DEFAULT_LIMIT : $limit;
    }

    public function currentPage(): int
    {
        $lastPage = $this->lastPage();

        if ($this->page > $lastPage) {
            return $lastPage;
        }

        return $this->page;
    }

    public function limit(): int
    {
        return $this->limit;
    }

    /**
     * @return int the count for the current page
     */
    public function count(): int
    {
        return $this->getPage()->count();
    }

    public function totalCount(): int
    {
        return $this->collection->count();
    }

    public function getIterator(): \Traversable
    {
        return $this->getPage()->getIterator();
    }

    public function nextPage(): ?int
    {
        $currentPage = $this->currentPage();

        if ($currentPage === $this->lastPage()) {
            return null;
        }

        return ++$currentPage;
    }

    public function previousPage(): ?int
    {
        $page = $this->currentPage();

        if (1 === $page) {
            return null;
        }

        return --$page;
    }

    public function firstPage(): int
    {
        return 1;
    }

    public function lastPage(): int
    {
        $totalCount = $this->totalCount();

        if (0 === $totalCount) {
            return 1;
        }

        return (int) \ceil($totalCount / $this->limit());
    }

    public function pageCount(): int
    {
        return $this->lastPage();
    }

    public function haveToPaginate(): bool
    {
        return $this->pageCount() > 1;
    }

    private function getPage(): Collection
    {
        if ($this->cachedPage) {
            return $this->cachedPage;
        }

        $offset = $this->currentPage() * $this->limit() - $this->limit();

        return $this->cachedPage = $this->collection->take($this->limit(), $offset);
    }
}
