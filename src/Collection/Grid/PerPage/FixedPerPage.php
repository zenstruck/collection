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
final class FixedPerPage implements PerPage
{
    /**
     * @param positive-int $value
     */
    public function __construct(private int $value = 20)
    {
    }

    public function value(?int $input): int
    {
        return $this->value;
    }
}
