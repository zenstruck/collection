<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Grid\Input;

use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Collection\ArrayCollection;
use Zenstruck\Collection\Grid\Input;
use Zenstruck\Collection\Specification\OrderBy;
use Zenstruck\Uri;
use Zenstruck\Uri\ParsedUri;

use function Zenstruck\collect;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class UriInput implements Input, \Stringable
{
    private const PAGE = 'page';
    private const PER_PAGE = 'perPage';
    private const SORT = 'sort';
    private const QUERY = 'q';
    private const FILTERS = 'filters';

    private ParsedUri $uri;

    /** @var mixed[] */
    private array $query;

    public function __construct(string|Request|Uri $uri, private ?string $key = null)
    {
        if (!\class_exists(ParsedUri::class)) {
            throw new \LogicException('The "zenstruck/uri" package is required to use UriInput. Run "composer require zenstruck/uri".');
        }

        $this->uri = ParsedUri::wrap($uri);
        $query = $key ? $this->uri->query()->get($key, []) : $this->uri->query()->all();
        $this->query = \is_array($query) ? $query : [];
    }

    public function __toString(): string
    {
        if (!$this->key) {
            return $this->uri->withQuery($this->query)->toString();
        }

        return $this->uri->withQueryParam($this->key, $this->query)->toString();
    }

    public function page(): int
    {
        if (\is_numeric($page = $this->query[self::PAGE] ?? 1)) {
            return (int) $page;
        }

        return 1;
    }

    public function applyPage(int $value): static
    {
        $clone = clone $this;
        $clone->query[self::PAGE] = $value;

        return $clone;
    }

    public function query(): ?string
    {
        $query = $this->query[self::QUERY] ?? null;

        return \is_scalar($query) ? (string) $query : null;
    }

    public function applyQuery(?string $value): static
    {
        $clone = clone $this;
        $clone->query[self::QUERY] = $value;

        return $clone;
    }

    public function perPage(): ?int
    {
        if (\is_numeric($page = $this->query[self::PER_PAGE] ?? null)) {
            return (int) $page;
        }

        return null;
    }

    public function applyPerPage(int $value): static
    {
        $clone = clone $this;
        $clone->query[self::PER_PAGE] = $value;

        return $clone;
    }

    public function sort(): ?OrderBy
    {
        $sort = $this->query[self::SORT] ?? null;

        return match (true) {
            \is_string($sort) && \str_starts_with($sort, '-') => OrderBy::desc(\mb_substr($sort, 1)),
            \is_string($sort) => OrderBy::asc($sort),
            default => null,
        };
    }

    public function applySort(OrderBy $orderBy): static
    {
        $clone = clone $this;
        $clone->query[self::SORT] = \sprintf('%s%s', $orderBy->isDesc() ? '-' : '', $orderBy->field);

        return $clone;
    }

    public function filter(string $name): mixed
    {
        return $this->filters()[$name] ?? null;
    }

    public function applyFilter(string $name, mixed $value): static
    {
        $filters = $this->filters();
        $filters[$name] = $value;

        $clone = clone $this;
        $clone->query[self::FILTERS] = $filters;

        return $clone;
    }

    public function reset(): static
    {
        $clone = clone $this;
        unset(
            $clone->query[self::PAGE],
            $clone->query[self::PER_PAGE],
            $clone->query[self::SORT],
            $clone->query[self::QUERY],
            $clone->query[self::FILTERS]
        );

        return $clone;
    }

    public function values(): ArrayCollection
    {
        return collect([
            self::PAGE => $this->query[self::PAGE] ?? null,
            self::PER_PAGE => $this->query[self::PER_PAGE] ?? null,
            self::SORT => $this->query[self::SORT] ?? null,
            self::QUERY => $this->query[self::QUERY] ?? null,
            self::FILTERS => $this->filters(),
        ])->filter();
    }

    /**
     * @return mixed[]
     */
    private function filters(): array
    {
        if (\is_array($filters = $this->query[self::FILTERS] ?? [])) {
            return $filters;
        }

        return [];
    }
}
