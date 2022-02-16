<?php

namespace Zenstruck\Collection\Specification\Filter;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class WildcardComparison extends Comparison
{
    private string $format = '%s';
    private ?string $wildcard = null;

    final public function __construct(string $field, ?string $value)
    {
        parent::__construct($field, $value);
    }

    final public static function contains(string $field, ?string $value): static
    {
        $specification = new static($field, $value);

        $specification->format = '%%%s%%';

        return $specification;
    }

    final public static function beginsWith(string $field, ?string $value): static
    {
        $specification = new static($field, $value);

        $specification->format = '%s%%';

        return $specification;
    }

    final public static function endsWith(string $field, ?string $value): static
    {
        $specification = new static($field, $value);

        $specification->format = '%%%s';

        return $specification;
    }

    final public function value(): string
    {
        $value = \sprintf($this->format, parent::value());

        if ($this->wildcard) {
            $value = \str_replace($this->wildcard, '%', $value);
        }

        return $value;
    }

    final public function allowWildcard(string $wildcard = '*'): self
    {
        $this->wildcard = $wildcard;

        return $this;
    }
}
