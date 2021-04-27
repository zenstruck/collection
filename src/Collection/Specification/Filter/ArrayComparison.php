<?php

namespace Zenstruck\Collection\Specification\Filter;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class ArrayComparison extends Comparison
{
    final public function __construct(string $field, array $value)
    {
        parent::__construct($field, $value);
    }

    final public function value(): array
    {
        return parent::value();
    }
}
