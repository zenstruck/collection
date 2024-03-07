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
final class SetPerPage implements PerPage
{
    /**
     * @param list<positive-int> $values
     * @param positive-int       $default
     */
    public function __construct(
        public readonly array $values = [20, 50, 100],
        public readonly int $default = 20,
    ) {
    }

    public function value(?int $input): int
    {
        return \in_array($input, $this->values, true) ? $input : $this->default;
    }
}
