<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Specification;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template C
 */
final class Callback
{
    /** @var callable(C):mixed */
    private $value;

    /**
     * @param callable(C):mixed $value
     */
    public function __construct(callable $value)
    {
        $this->value = $value;
    }

    /**
     * @return callable(C):mixed
     */
    public function value(): callable
    {
        return $this->value;
    }
}
