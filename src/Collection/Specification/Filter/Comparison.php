<?php

namespace Zenstruck\Collection\Specification\Filter;

use Zenstruck\Collection\Specification\Field;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class Comparison extends Field
{
    private mixed $value;

    public function __construct(string $field, mixed $value)
    {
        parent::__construct($field);

        $this->value = $value;
    }

    public function __toString(): string
    {
        $value = $this->value();

        if (\is_string($value)) {
            $value = "'{$value}'";
        }

        return \sprintf('Compare(%s %s %s)',
            $this->field(),
            (new \ReflectionClass($this))->getShortName(),
            \is_scalar($value) ? $value : \get_debug_type($value)
        );
    }

    public function value(): mixed
    {
        return $this->value;
    }
}
