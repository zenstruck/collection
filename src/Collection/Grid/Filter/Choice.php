<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Grid\Filter;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Choice implements \Stringable
{
    public function __construct(
        public readonly ?string $value,
        public readonly ?object $specification = null,
        public readonly ?string $label = null,
    ) {
    }

    public function __toString(): string
    {
        return $this->label ?? $this->value ?? '';
    }
}
