<?php

namespace Zenstruck\Collection\Specification\Normalizer;

use Zenstruck\Collection\Specification\Normalizer;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
trait HasNormalizer
{
    private ?Normalizer $normalizer = null;

    public function setNormalizer(Normalizer $normalizer): void
    {
        $this->normalizer = $normalizer;
    }

    protected function normalizer(): Normalizer
    {
        if (!$this->normalizer) {
            throw new \RuntimeException('A normalizer has not been set.');
        }

        return $this->normalizer;
    }
}
