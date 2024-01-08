<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Specification\Filter;

use Zenstruck\Collection\Specification\Comparison;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @method mixed[] value()
 */
final class In extends Comparison
{
    /**
     * @param mixed[] $value
     */
    public function __construct(string $field, array $value)
    {
        parent::__construct($field, $value);
    }
}
