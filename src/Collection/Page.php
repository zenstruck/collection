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

    /** @var positive-int */
    private int $resolvedPage;

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
     * When enabled, a page past the end of the collection falls back to the
     * last page - {@see currentPage} reports it and the page's items are the
     * last page's.
     *
     * The fallback is what requires counting the collection, so it's only
     * paid for when the requested page really is out of range.
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

        $this->resolve();

        return $this->resolvedPage;
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
     * @return Collection<V,K>
     */
    private function getPage(): Collection
    {
        $this->resolve();

        return $this->cachedPage;
    }

    /**
     * Fetches the page, falling back to the last page in strict mode if the
     * requested one turned out to be past the end. Only that fallback needs
     * the collection counted.
     */
    private function resolve(): void
    {
        if (isset($this->cachedPage)) {
            return;
        }

        $items = $this->fetch($page = $this->page);

        if ($this->strict && $page > 1 && !$items->count()) {
            $items = $this->fetch($page = $this->lastPage());
        }

        $this->resolvedPage = $page;
        $this->hasMorePages = $items->count() > $this->limit();
        $this->cachedPage = $items->take($this->limit());
    }

    /**
     * Fetches one more item than fits on the page - its presence is what
     * {@see hasMorePages} reports, and it's dropped by {@see resolve}.
     *
     * @param positive-int $page
     *
     * @return ArrayCollection<V,K&array-key>
     */
    private function fetch(int $page): ArrayCollection
    {
        return $this->collection->take($this->limit() + 1, $page * $this->limit() - $this->limit())->eager();
    }
}
