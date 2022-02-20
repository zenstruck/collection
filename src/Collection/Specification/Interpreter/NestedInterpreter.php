<?php

namespace Zenstruck\Collection\Specification\Interpreter;

use Zenstruck\Collection\Specification\Interpreter;
use Zenstruck\Collection\Specification\Nested;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class NestedInterpreter implements Interpreter, InterpreterAware
{
    use HasInterpreter;

    /**
     * @param Nested $specification
     */
    public function interpret($specification, mixed $context): mixed
    {
        return $this->interpreter()->interpret($specification->child(), $context);
    }

    public function supports(mixed $specification, mixed $context): bool
    {
        return $specification instanceof Nested;
    }
}
