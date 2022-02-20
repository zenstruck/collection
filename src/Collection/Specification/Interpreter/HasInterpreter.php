<?php

namespace Zenstruck\Collection\Specification\Interpreter;

use Zenstruck\Collection\Specification\Interpreter;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
trait HasInterpreter
{
    private ?Interpreter $interpreter = null;

    public function setInterpreter(Interpreter $interpreter): void
    {
        $this->interpreter = $interpreter;
    }

    protected function interpreter(): Interpreter
    {
        if (!$this->interpreter) {
            throw new \RuntimeException('An interpreter has not been set.');
        }

        return $this->interpreter;
    }
}
