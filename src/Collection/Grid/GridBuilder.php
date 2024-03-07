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

use Zenstruck\Collection\Grid;
use Zenstruck\Collection\Grid\Definition\ColumnDefinition;
use Zenstruck\Collection\Grid\Filter\AutoFilter;
use Zenstruck\Collection\Matchable;
use Zenstruck\Collection\Specification\OrderBy;

use function Zenstruck\collect;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template T of array<string,mixed>|object
 */
final class GridBuilder
{
    /** @var Matchable<mixed,T>|null */
    public ?Matchable $source = null;
    public ?OrderBy $defaultSort = null;
    public ?PerPage $perPage = null;
    public ?object $defaultSpecification = null;

    /** @var array<string,ColumnDefinition> */
    private array $columns = [];

    /** @var array<string,Filter> */
    private array $filters = [];

    /**
     * @return Grid<T>
     */
    public function build(Input $input): Grid
    {
        $columns = collect($this->columns)
            ->map(fn(ColumnDefinition $column) => new Column(
                definition: $column,
                input: $input,
            ))
        ;

        return new Grid(
            input: $input,
            source: $this->source ?? throw new \LogicException('No source defined.'),
            columns: new Columns($columns, $input, $this->defaultSort),
            filters: new Filters($this->filters),
            perPage: $this->perPage,
            defaultSpecification: $this->defaultSpecification,
        );
    }

    /**
     * @param OrderBy::*|null $defaultSort
     *
     * @return $this
     */
    public function addColumn(
        string $name,
        bool $searchable = false,
        bool $sortable = false,
        bool $autofilter = false,
        ?string $defaultSort = null,
    ): self {
        $this->columns[$name] = new ColumnDefinition(
            name: $name,
            searchable: $searchable,
            sortable: $sortable,
        );

        if ($defaultSort) {
            $this->defaultSort = new OrderBy($name, $defaultSort);
        }

        if ($autofilter) {
            $this->addFilter($name, new AutoFilter($name));
        }

        return $this;
    }

    public function getColumn(string $name): ColumnDefinition
    {
        return $this->columns[$name] ?? throw new \InvalidArgumentException(\sprintf('Column "%s" does not exist.', $name));
    }

    /**
     * @return $this
     */
    public function removeColumn(string $name): self
    {
        unset($this->columns[$name]);

        return $this;
    }

    /**
     * @return $this
     */
    public function addFilter(string $name, Filter $filter): self
    {
        $this->filters[$name] = $filter;

        return $this;
    }

    public function getFilter(string $name): Filter
    {
        return $this->filters[$name] ?? throw new \InvalidArgumentException(\sprintf('Filter "%s" does not exist.', $name));
    }

    /**
     * @return $this
     */
    public function removeFilter(string $name): self
    {
        unset($this->filters[$name]);

        return $this;
    }
}
