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
 * @implements \IteratorAggregate<K,V>
 */
final class Page implements \IteratorAggregate, \Countable
{
    public const DEFAULT_LIMIT = 20;

    /** @var positive-int */
    private int $page;

    /** @var positive-int */
    private int $limit;
    private bool $strict = false;
    private bool $hasMorePages;

    /** @var non-negative-int */
    private int $totalCount;

    /** @var Collection<V,K> */
    private Collection $cachedPage;

    /**
     * @param Collection<V,K> $collection
     * @param positive-int    $page
     * @param positive-int    $limit
     */
    public function __construct(private Collection $collection, int $page = 1, int $limit = self::DEFAULT_LIMIT)
    {
        $this->page = \max($page, 1);
        $this->limit = $limit < 1 ? self::DEFAULT_LIMIT : $limit;
    }

    /**
     * Enable/Disable "strict mode".
     *
     * When enabled, when calling {@see currentPage}, if provided page number
     * greater than the calculated last page number, the last page number will
     * be returned.
     *
     * When enabled, extra work (ie count query) may be required to ensure the
     * current page number is valid.
     *
     * @return $this
     */
    public function strict(bool $flag = true): self
    {
        $this->strict = $flag;

        return $this;
    }

    public function currentPage(): int
    {
        if (!$this->strict) {
            return $this->page;
        }

        $lastPage = $this->lastPage();

        if ($this->page > $lastPage) {
            return $lastPage;
        }

        return $this->page;
    }

    /**
     * @return positive-int
     */
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
        return $this->totalCount ??= $this->collection->count();
    }

    public function getIterator(): \Traversable
    {
        return $this->getPage()->getIterator();
    }

    /**
     * Whether there is at least one more page after this one. Unlike
     * {@see lastPage}, this doesn't require counting the collection.
     */
    public function hasMorePages(): bool
    {
        $this->getPage();

        return $this->hasMorePages;
    }

    public function nextPage(): ?int
    {
        if (!$this->hasMorePages()) {
            return null;
        }

        return $this->currentPage() + 1;
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

    /**
     * @return positive-int
     */
    public function lastPage(): int
    {
        $totalCount = $this->totalCount();

        if (0 === $totalCount) {
            return 1;
        }

        return (int) \ceil($totalCount / $this->limit()); // @phpstan-ignore return.type
    }

    /**
     * @return positive-int
     */
    public function pageCount(): int
    {
        return $this->lastPage();
    }

    public function haveToPaginate(): bool
    {
        return $this->currentPage() > 1 || $this->hasMorePages();
    }

    /**
     * Fetches one more item than fits on the page - its presence is what
     * {@see hasMorePages} reports, and it's dropped before returning.
     *
     * @return Collection<V,K>
     */
    private function getPage(): Collection
    {
        if (isset($this->cachedPage)) {
            return $this->cachedPage;
        }

        $offset = $this->currentPage() * $this->limit() - $this->limit();
        $items = $this->collection->take($this->limit() + 1, $offset)->eager();
        $this->hasMorePages = $items->count() > $this->limit();

        return $this->cachedPage = $items->take($this->limit());
    }
}
