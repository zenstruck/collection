<?php

namespace Zenstruck\Collection\Doctrine\Specification\Normalizer;

use Zenstruck\Collection\Doctrine\Specification\Context;
use Zenstruck\Collection\Specification\Normalizer;
use Zenstruck\Collection\Specification\Normalizer\SplitSupports;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class DoctrineNormalizer implements Normalizer
{
    use SplitSupports;

    protected function supportsContext(mixed $context): bool
    {
        return $context instanceof Context;
    }
}
