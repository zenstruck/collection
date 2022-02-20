<?php

namespace Zenstruck\Collection\Doctrine\Specification\Interpreter;

use Zenstruck\Collection\Doctrine\Specification\Context;
use Zenstruck\Collection\Specification\Interpreter;
use Zenstruck\Collection\Specification\Interpreter\SplitSupports;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class DoctrineInterpreter implements Interpreter
{
    use SplitSupports;

    protected function supportsContext(mixed $context): bool
    {
        return $context instanceof Context;
    }
}
