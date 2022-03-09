<?php

namespace Zenstruck\Collection\Tests\Fixture;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Stringable implements \Stringable
{
    public function __construct(private string $value)
    {
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
