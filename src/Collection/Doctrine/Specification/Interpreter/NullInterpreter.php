<?php

namespace Zenstruck\Collection\Doctrine\Specification\Interpreter;

use Zenstruck\Collection\Doctrine\Specification\Context;
use Zenstruck\Collection\Specification\Field;
use Zenstruck\Collection\Specification\Filter\IsNotNull;
use Zenstruck\Collection\Specification\Filter\IsNull;
use Zenstruck\Collection\Specification\Interpreter\ClassMethodMap;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class NullInterpreter extends DoctrineInterpreter
{
    use ClassMethodMap;

    /**
     * @param Field   $specification
     * @param Context $context
     */
    public function interpret($specification, $context): string
    {
        return $context->qb()->expr()->{self::methodFor($specification)}($context->prefixAlias($specification->field()));
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
