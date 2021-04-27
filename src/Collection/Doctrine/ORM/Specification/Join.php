<?php

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
    private string $type;
    private bool $eager = false;
    private $child;

    private function __construct(string $type, string $field, ?string $alias = null)
    {
        parent::__construct($field);

        $this->type = $type;
        $this->alias = $alias ?? $field;
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

    public function scope($specification): self
    {
        $this->child = $specification;

        return $this;
    }

    public function alias(): string
    {
        return $this->alias;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function isEager(): bool
    {
        return $this->eager;
    }

    /**
     * @return mixed
     */
    public function child()
    {
        return $this->child;
    }
}
