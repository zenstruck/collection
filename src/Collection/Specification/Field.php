<?php

namespace Zenstruck\Collection\Specification;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class Field implements \Stringable
{
    private string $field;

    public function __construct(string $field)
    {
        $this->field = $field;
    }

    public function __toString(): string
    {
        return \sprintf('%s(%s)', (new \ReflectionClass($this))->getShortName(), $this->field());
    }

    final public function field(): string
    {
        return $this->field;
    }
}
