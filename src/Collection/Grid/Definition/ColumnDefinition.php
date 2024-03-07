<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Grid\Definition;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ColumnDefinition
{
    public function __construct(
        public string $name,
        public bool $searchable = false,
        public bool $sortable = false,
    ) {
    }
}
