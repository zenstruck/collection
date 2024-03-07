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
use Zenstruck\Collection\Specification\OrderBy;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @implements \IteratorAggregate<string,Column>
 */
final class Columns implements \IteratorAggregate, \Countable
{
    private self $searchable;
    private self $sortable;

    /**
     * @internal
     *
     * @param ArrayCollection<string,Column> $columns
     */
    public function __construct(private ArrayCollection $columns, private Input $input, private ?OrderBy $defaultSort)
    {
    }

    public function get(string $name): ?Column
    {
        return $this->columns->get($name);
    }

    public function has(string $name): bool
    {
        return $this->columns->has($name);
    }

    public function searchable(): self
    {
        return $this->searchable ??= new self($this->columns->filter(fn(Column $c) => $c->isSearchable()), $this->input, $this->defaultSort);
    }

    public function sortable(): self
    {
        return $this->sortable ??= new self($this->columns->filter(fn(Column $c) => $c->isSortable()), $this->input, $this->defaultSort);
    }

    public function sort(): ?OrderBy
    {
        if (!$sort = $this->input->sort()) {
            return $this->defaultSort;
        }

        return $this->sortable()->has($sort->field) ? $sort : $this->defaultSort;
    }

    /**
     * @return ArrayCollection<string,Column>
     */
    public function all(): ArrayCollection
    {
        return $this->columns;
    }

    public function getIterator(): \Traversable
    {
        return $this->columns;
    }

    public function count(): int
    {
        return $this->columns->count();
    }
}
