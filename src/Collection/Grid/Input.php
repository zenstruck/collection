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
 * @immutable
 */
interface Input
{
    public function page(): int;

    /**
     * @param positive-int $value
     */
    public function applyPage(int $value): static;

    public function query(): ?string;

    public function applyQuery(?string $value): static;

    public function perPage(): ?int;

    /**
     * @param positive-int $value
     */
    public function applyPerPage(int $value): static;

    public function sort(): ?OrderBy;

    public function applySort(OrderBy $orderBy): static;

    public function filter(string $name): mixed;

    public function applyFilter(string $name, mixed $value): static;

    public function reset(): static;

    /**
     * @return ArrayCollection<string,mixed>
     */
    public function values(): ArrayCollection;
}
