<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Grid\PerPage;

use Zenstruck\Collection\Grid\PerPage;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class RangePerPage implements PerPage
{
    /**
     * @param positive-int $min
     * @param positive-int $max
     * @param positive-int $default
     */
    public function __construct(
        public readonly int $min = 1,
        public readonly int $max = 100,
        public readonly int $default = 20,
    ) {
    }

    public function value(?int $input): int
    {
        return \max($this->min, \min($this->max, $input ?? $this->default));
    }
}
