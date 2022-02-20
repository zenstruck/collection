<?php

namespace Zenstruck\Collection\Specification;

use Zenstruck\Collection\Specification\Interpreter\InterpreterAware;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class SpecificationInterpreter implements Interpreter
{
    /** @var array<string,array<string,Interpreter>> */
    private array $interpreterCache = [];

    /**
     * @param iterable<Interpreter> $interpreters
     */
    public function __construct(private iterable $interpreters)
    {
    }

    public function interpret(mixed $specification, mixed $context): mixed
    {
        if (!$interpreter = $this->getInterpreter($specification, $context)) {
            throw new \RuntimeException(\sprintf('Specification "%s" with context "%s" does not have a supported interpreter registered.', self::stringify($specification), \get_debug_type($context)));
        }

        return $interpreter->interpret($specification, $context);
    }

    public function supports(mixed $specification, mixed $context): bool
    {
        return null !== $this->getInterpreter($specification, $context);
    }

    /**
     * Utility method to aid in debugging.
     */
    public static function stringify(mixed $specification): string
    {
        if ($specification instanceof \Stringable) {
            return $specification;
        }

        if ($specification instanceof Nested) {
            return \sprintf('%s(%s)', $specification::class, self::stringify($specification->child()));
        }

        return \get_debug_type($specification);
    }

    private function getInterpreter(mixed $specification, mixed $context): ?Interpreter
    {
        $specificationCacheKey = \is_object($specification) ? $specification::class : 'native-'.\gettype($specification);
        $contextCacheKey = \is_object($context) ? $context::class : 'native-'.\gettype($context);

        if (isset($this->interpreterCache[$specificationCacheKey][$contextCacheKey])) {
            return $this->interpreterCache[$specificationCacheKey][$contextCacheKey];
        }

        foreach ($this->interpreters as $interpreter) {
            if (!$interpreter->supports($specification, $context)) {
                continue;
            }

            if ($interpreter instanceof InterpreterAware) {
                $interpreter->setInterpreter($this);
            }

            return $this->interpreterCache[$specificationCacheKey][$contextCacheKey] = $interpreter;
        }

        return null;
    }
}
