<?php

namespace Zenstruck\Collection\Specification\Interpreter;

use Zenstruck\Collection\Specification\Interpreter;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface InterpreterAware
{
    public function setInterpreter(Interpreter $interpreter): void;
}
