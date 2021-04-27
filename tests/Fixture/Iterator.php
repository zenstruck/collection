<?php

namespace Zenstruck\Collection\Tests\Fixture;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Iterator implements \Iterator
{
    private array $items;
    private int $position = 0;

    public function __construct(int $count)
    {
        $this->items = $count ? \range(1, $count) : [];
    }

    public function current()
    {
        return $this->items[$this->position];
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function key()
    {
        return $this->position;
    }

    public function valid(): bool
    {
        return isset($this->items[$this->position]);
    }

    public function rewind(): void
    {
        $this->position = 0;
    }
}
