<?php

namespace Zenstruck\Collection\Specification\Filter;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class ArrayComparison extends Comparison
{
    /**
     * @param mixed[] $value
     */
    final public function __construct(string $field, array $value)
    {
        parent::__construct($field, $value);
    }

    /**
     * @return mixed[]
     */
    final public function value(): array
    {
        return parent::value();
    }
}
