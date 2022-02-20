<?php

namespace Zenstruck\Collection\Doctrine\Specification\Interpreter;

use Zenstruck\Collection\Doctrine\Specification\Context;
use Zenstruck\Collection\Specification\Interpreter\ClassMethodMap;
use Zenstruck\Collection\Specification\Interpreter\HasInterpreter;
use Zenstruck\Collection\Specification\Interpreter\InterpreterAware;
use Zenstruck\Collection\Specification\Logic\AndX;
use Zenstruck\Collection\Specification\Logic\Composite;
use Zenstruck\Collection\Specification\Logic\OrX;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class CompositeInterpreter extends DoctrineInterpreter implements InterpreterAware
{
    use ClassMethodMap, HasInterpreter;

    /**
     * @param Composite $specification
     * @param Context   $context
     */
    public function interpret(mixed $specification, mixed $context): mixed
    {
        $children = \array_filter(\array_map(
            fn($child) => $this->interpreter()->interpret($child, $context),
            $specification->children()
        ));

        if (empty($children)) {
            return null;
        }

        return $context->qb()->expr()->{self::methodFor($specification)}(...$children);
    }

    /**
     * @return array<class-string, string>
     */
    protected static function classMethodMap(): array
    {
        return [
            AndX::class => 'andX',
            OrX::class => 'orX',
        ];
    }
}
