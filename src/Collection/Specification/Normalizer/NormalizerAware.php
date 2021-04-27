<?php

namespace Zenstruck\Collection\Specification\Normalizer;

use Zenstruck\Collection\Specification\Normalizer;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface NormalizerAware
{
    public function setNormalizer(Normalizer $normalizer): void;
}
