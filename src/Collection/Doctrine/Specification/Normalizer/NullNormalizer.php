<?php

namespace Zenstruck\Collection\Doctrine\Specification\Normalizer;

use Zenstruck\Collection\Doctrine\Specification\Context;
use Zenstruck\Collection\Specification\Field;
use Zenstruck\Collection\Specification\Filter\IsNotNull;
use Zenstruck\Collection\Specification\Filter\IsNull;
use Zenstruck\Collection\Specification\Normalizer\ClassMethodMap;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class NullNormalizer extends DoctrineNormalizer
{
    use ClassMethodMap;

    /**
     * @param Field   $specification
     * @param Context $context
     */
    public function normalize($specification, $context): string
    {
        return $context->qb()->expr()->{self::methodFor($specification)}($context->prefixAlias($specification->field()));
    }

    /**
     * @param Field $specification
     */
    public function stringify(mixed $specification, mixed $context): string
    {
        return \sprintf('%s(%s)', self::methodFor($specification), $specification->field());
    }

    /**
     * @return array<class-string, string>
     */
    protected static function classMethodMap(): array
    {
        return [
            IsNull::class => 'isNull',
            IsNotNull::class => 'isNotNull',
        ];
    }
}
