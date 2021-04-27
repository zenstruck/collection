<?php

namespace Zenstruck\Collection\Specification;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class Field
{
    private string $field;

    public function __construct(string $field)
    {
        $this->field = $field;
    }

    final public function field(): string
    {
        return $this->field;
    }
}
