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

use Zenstruck\Collection\Grid\Column;
use Zenstruck\Collection\Grid\Columns;
use Zenstruck\Collection\Grid\Filter;
use Zenstruck\Collection\Grid\Filters;
use Zenstruck\Collection\Grid\Input;
use Zenstruck\Collection\Grid\PerPage;
use Zenstruck\Collection\Grid\PerPage\FixedPerPage;
use Zenstruck\Collection\Specification\Filter\Contains;
use Zenstruck\Collection\Specification\Logic\AndX;
use Zenstruck\Collection\Specification\Logic\OrX;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template T of array<string,mixed>|object
 *
 * @implements \IteratorAggregate<T>
 */
final class Grid implements \IteratorAggregate
{
    public readonly PerPage $perPage;

    /** @var Page<int,T> */
    private Page $page;

    /**
     * @internal
     *
     * @param Matchable<mixed,T> $source
     */
    public function __construct(
        public readonly Input $input,
        public readonly Matchable $source,
        public readonly Columns $columns,
        public readonly Filters $filters,
        ?PerPage $perPage = null,
        private ?object $defaultSpecification = null,
    ) {
        $this->perPage = $perPage ?? new FixedPerPage();
    }

    public function getIterator(): \Traversable
    {
        return $this->page();
    }

    /**
     * @return Page<int,T>
     */
    public function page(): Page
    {
        if (isset($this->page)) {
            return $this->page;
        }

        $specification = new AndX(...\array_filter([
            $this->defaultSpecification,
            $this->columns->sort(),
            $this->searchSpecification(),
            new AndX(...\array_filter($this->filterSpecification())),
        ]));

        return $this->page = $this->source->filter($specification)
            ->paginate($this->input->page(), $this->perPage->value($this->input->perPage()))->strict()
        ;
    }

    private function searchSpecification(): OrX
    {
        if (!$query = $this->input->query()) {
            return new OrX();
        }

        return new OrX(...$this->columns
            ->searchable()
            ->all()
            ->map(fn(Column $column) => new Contains($column->name(), $query))
            ->values()
            ->all()
        );
    }

    /**
     * @return list<object|null>
     */
    private function filterSpecification(): array
    {
        return $this->filters->all()
            ->map(fn(Filter $filter, string $name) => $filter->apply($this->input->filter($name)))
            ->values()
            ->all()
        ;
    }
}
