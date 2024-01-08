<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Doctrine\ORM\Specification;

use Zenstruck\Collection\Specification\Field;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Join extends Field
{
    private const TYPE_INNER = 'inner';
    private const TYPE_LEFT = 'left';

    private string $alias;
    private bool $eager = false;
    private mixed $child = null;

    /**
     * @param self::TYPE_INNER|self::TYPE_LEFT $type
     */
    private function __construct(private string $type, string $field, ?string $alias = null)
    {
        parent::__construct($field);

        $this->alias = $alias ?? $field;
    }

    public function __toString(): string
    {
        return \sprintf('%sJoin(%s)', \ucfirst($this->type()), $this->field());
    }

    public static function inner(string $field, ?string $alias = null): self
    {
        return new self(self::TYPE_INNER, $field, $alias);
    }

    public static function left(string $field, ?string $alias = null): self
    {
        return new self(self::TYPE_LEFT, $field, $alias);
    }

    public static function anti(string $field): AntiJoin
    {
        return new AntiJoin($field);
    }

    public function eager(): self
    {
        $this->eager = true;

        return $this;
    }

    public function scope(mixed $specification): self
    {
        $this->child = $specification;

        return $this;
    }

    public function alias(): string
    {
        return $this->alias;
    }

    /**
     * @return self::TYPE_INNER|self::TYPE_LEFT
     */
    public function type(): string
    {
        return $this->type;
    }

    public function isEager(): bool
    {
        return $this->eager;
    }

    public function child(): mixed
    {
        return $this->child;
    }
}
