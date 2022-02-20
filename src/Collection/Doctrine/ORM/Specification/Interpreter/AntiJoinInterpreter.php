<?php

namespace Zenstruck\Collection\Doctrine\ORM\Specification\Interpreter;

use Zenstruck\Collection\Doctrine\ORM\Specification\AntiJoin;
use Zenstruck\Collection\Doctrine\ORM\Specification\ORMContext;
use Zenstruck\Collection\Specification\Interpreter;
use Zenstruck\Collection\Specification\Interpreter\SplitSupports;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class AntiJoinInterpreter implements Interpreter
{
    use SplitSupports;

    /**
     * @param AntiJoin   $specification
     * @param ORMContext $context
     */
    public function interpret(mixed $specification, mixed $context): mixed
    {
        $context->qb()
            ->leftJoin($context->prefixAlias($specification->field()), $specification->field())
            ->andWhere("{$specification->field()} IS NULL")
        ;

        return null;
    }

    protected function supportsSpecification(mixed $specification): bool
    {
        return $specification instanceof AntiJoin;
    }

    protected function supportsContext(mixed $context): bool
    {
        return $context instanceof ORMContext;
    }
}
