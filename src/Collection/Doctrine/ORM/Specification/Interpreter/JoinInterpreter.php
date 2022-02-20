<?php

namespace Zenstruck\Collection\Doctrine\ORM\Specification\Interpreter;

use Zenstruck\Collection\Doctrine\ORM\Specification\Join;
use Zenstruck\Collection\Doctrine\ORM\Specification\ORMContext;
use Zenstruck\Collection\Specification\Interpreter;
use Zenstruck\Collection\Specification\Interpreter\HasInterpreter;
use Zenstruck\Collection\Specification\Interpreter\InterpreterAware;
use Zenstruck\Collection\Specification\Interpreter\SplitSupports;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class JoinInterpreter implements Interpreter, InterpreterAware
{
    use HasInterpreter, SplitSupports;

    /**
     * @param Join       $join
     * @param ORMContext $context
     */
    public function interpret(mixed $join, mixed $context): mixed
    {
        $this->addJoin($context, $join);

        if ($join->isEager()) {
            $context->qb()->addSelect($join->alias());
        }

        if (null === $join->child()) {
            return null;
        }

        return $this->interpreter()->interpret($join->child(), $context->scopeTo($join->alias()));
    }

    protected function supportsSpecification(mixed $specification): bool
    {
        return $specification instanceof Join;
    }

    protected function supportsContext(mixed $context): bool
    {
        return $context instanceof ORMContext;
    }

    private function addJoin(ORMContext $context, Join $join): void
    {
        $joinString = $context->prefixAlias($join->field());

        foreach ($context->qb()->getDQLParts()['join'] as $entry) {
            foreach ($entry as $item) {
                if ($joinString === $item->getJoin()) {
                    return;
                }
            }
        }

        $context->qb()->{$join->type().'Join'}($joinString, $join->alias());
    }
}
