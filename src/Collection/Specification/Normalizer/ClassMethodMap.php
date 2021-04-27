<?php

namespace Zenstruck\Collection\Specification\Normalizer;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
trait ClassMethodMap
{
    protected static function classInMap($specification): bool
    {
        if (!\is_object($specification)) {
            return false;
        }

        return \array_key_exists(\get_class($specification), static::classMethodMap());
    }

    protected static function methodFor(object $specification): string
    {
        return static::classMethodMap()[\get_class($specification)];
    }

    /**
     * @return array<string, string>
     */
    abstract protected static function classMethodMap(): array;

    protected function supportsSpecification($specification): bool
    {
        return self::classInMap($specification);
    }
}
