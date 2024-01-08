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
 */
abstract class Comparison extends Field
{
    public function __construct(string $field, private mixed $value)
    {
        parent::__construct($field);
    }

    final public function __toString(): string
    {
        $value = $this->value;

        if (\is_string($value)) {
            $value = "'{$value}'";
        }

        return \sprintf('Compare(%s %s %s)',
            $this->field(),
            (new \ReflectionClass($this))->getShortName(),
            \is_scalar($value) ? $value : \get_debug_type($value),
        );
    }

    final public function value(): mixed
    {
        return $this->value;
    }
}
