<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Grid\Filter;

use Zenstruck\Collection\Grid\Filter;
use Zenstruck\Collection\Specification\Filter\Between;
use Zenstruck\Collection\Specification\Filter\Contains;
use Zenstruck\Collection\Specification\Filter\EndsWith;
use Zenstruck\Collection\Specification\Filter\EqualTo;
use Zenstruck\Collection\Specification\Filter\GreaterThan;
use Zenstruck\Collection\Specification\Filter\GreaterThanOrEqualTo;
use Zenstruck\Collection\Specification\Filter\In;
use Zenstruck\Collection\Specification\Filter\IsNull;
use Zenstruck\Collection\Specification\Filter\LessThan;
use Zenstruck\Collection\Specification\Filter\LessThanOrEqualTo;
use Zenstruck\Collection\Specification\Filter\StartsWith;
use Zenstruck\Collection\Specification\Logic\Not;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class AutoFilter implements Filter
{
    private const MAX_NEGATION_DEPTH = 32;

    public function __construct(private string $field)
    {
    }

    public function apply(mixed $value): ?object
    {
        if (\is_array($value) && \array_is_list($value)) {
            return new In($this->field, $value);
        }

        if (!\is_string($value) || !$value) {
            return null;
        }

        if (2 === \count($parts = \explode('...', $value))) {
            [$begin, $end] = $parts;
            $type = \str_starts_with($begin, '(') ? '(' : '[';
            $type .= \str_ends_with($end, ')') ? ')' : ']';

            return new Between($this->field, \trim($begin, '[('), \trim($end, ')]'), $type);
        }

        return match (true) {
            '~' === $value => new IsNull($this->field),
            \str_starts_with($value, '*') && \str_ends_with($value, '*') => new Contains($this->field, \mb_substr($value, 1, -1)),
            \str_starts_with($value, '*') => new StartsWith($this->field, \mb_substr($value, 1)),
            \str_ends_with($value, '*') => new EndsWith($this->field, \mb_substr($value, 0, -1)),
            \str_starts_with($value, '!') => $this->negate($value),
            \str_starts_with($value, '<=') => new LessThanOrEqualTo($this->field, \mb_substr($value, 2)),
            \str_starts_with($value, '>=') => new GreaterThanOrEqualTo($this->field, \mb_substr($value, 2)),
            \str_starts_with($value, '>') => new GreaterThan($this->field, \mb_substr($value, 1)),
            \str_starts_with($value, '<') => new LessThan($this->field, \mb_substr($value, 1)),
            \count($values = \explode(',', $value)) > 1 => new In($this->field, $values),
            default => new EqualTo($this->field, $value),
        };
    }

    private function negate(string $value): Not
    {
        if (\strspn($value, '!') > self::MAX_NEGATION_DEPTH) {
            throw new \InvalidArgumentException(\sprintf('Cannot negate a filter more than %d times.', self::MAX_NEGATION_DEPTH));
        }

        return new Not($this->apply(\mb_substr($value, 1, null, '8bit')));
    }
}
