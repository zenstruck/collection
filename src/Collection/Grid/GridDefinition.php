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
 *
 * @template T of array<string,mixed>|object
 */
interface GridDefinition
{
    /**
     * @param GridBuilder<T> $builder
     */
    public function configure(GridBuilder $builder): void;
}
