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
final class OrderBy extends Field
{
    public const ASC = 'ASC';
    public const DESC = 'DESC';

    /** @var self::* */
    public readonly string $direction;

    /**
     * @param self::* $direction
     */
    public function __construct(string $field, string $direction)
    {
        parent::__construct($field);

        $this->direction = match (\mb_strtoupper($direction)) {
            self::DESC => self::DESC,
            default => self::ASC,
        };
    }

    public static function asc(string $field): self
    {
        return new self($field, self::ASC);
    }

    public static function desc(string $field): self
    {
        return new self($field, self::DESC);
    }

    public function opposite(): self
    {
        return new self($this->field, self::ASC === $this->direction ? self::DESC : self::ASC);
    }

    public function isAsc(): bool
    {
        return self::ASC === $this->direction;
    }

    public function isDesc(): bool
    {
        return self::DESC === $this->direction;
    }
}
