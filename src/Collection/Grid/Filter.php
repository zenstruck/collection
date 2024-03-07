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

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface Filter
{
    public function apply(mixed $value): ?object;
}
