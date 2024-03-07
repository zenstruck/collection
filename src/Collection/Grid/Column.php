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

use Zenstruck\Collection\Grid\Definition\ColumnDefinition;
use Zenstruck\Collection\Specification\OrderBy;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Column
{
    /**
     * @internal
     */
    public function __construct(
        private ColumnDefinition $definition,
        private Input $input,
    ) {
    }

    public function name(): string
    {
        return $this->definition->name;
    }

    public function isSearchable(): bool
    {
        return $this->definition->searchable;
    }

    public function isSortable(): bool
    {
        return $this->definition->sortable;
    }

    public function sort(): ?OrderBy
    {
        if (!($sort = $this->input->sort()) || $this->name() !== $sort->field) {
            return null;
        }

        return $sort;
    }

    public function applyAscSort(): Input
    {
        return $this->input->applySort(OrderBy::asc($this->name()));
    }

    public function applyDescSort(): Input
    {
        return $this->input->applySort(OrderBy::desc($this->name()));
    }

    public function applyOppositeSort(): Input
    {
        if (!$sort = $this->sort()) {
            return $this->applyAscSort();
        }

        return $this->input->applySort($sort->opposite());
    }
}
