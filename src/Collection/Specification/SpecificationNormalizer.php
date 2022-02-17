<?php

namespace Zenstruck\Collection\Specification;

use Zenstruck\Collection\Specification\Normalizer\NormalizerAware;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class SpecificationNormalizer implements Normalizer
{
    /** @var iterable<Normalizer> */
    private iterable $normalizers;

    /** @var array<string,array<string,Normalizer>> */
    private array $normalizerCache = [];

    /**
     * @param iterable<Normalizer> $normalizers
     */
    public function __construct(iterable $normalizers)
    {
        $this->normalizers = $normalizers;
    }

    public function normalize(mixed $specification, mixed $context): mixed
    {
        return $this->getNormalizer($specification, $context)->normalize($specification, $context);
    }

    public function stringify(mixed $specification, mixed $context): string
    {
        return $this->getNormalizer($specification, $context)->stringify($specification, $context);
    }

    public function supports(mixed $specification, mixed $context): bool
    {
        try {
            $this->getNormalizer($specification, $context);

            return true;
        } catch (\RuntimeException) {
            return false;
        }
    }

    private function getNormalizer(mixed $specification, mixed $context): Normalizer
    {
        $specificationCacheKey = \is_object($specification) ? $specification::class : 'native-'.\gettype($specification);
        $contextCacheKey = \is_object($context) ? $context::class : 'native-'.\gettype($context);

        if (isset($this->normalizerCache[$specificationCacheKey][$contextCacheKey])) {
            return $this->normalizerCache[$specificationCacheKey][$contextCacheKey];
        }

        foreach ($this->normalizers as $normalizer) {
            if (!$normalizer->supports($specification, $context)) {
                continue;
            }

            if ($normalizer instanceof NormalizerAware) {
                $normalizer->setNormalizer($this);
            }

            return $this->normalizerCache[$specificationCacheKey][$contextCacheKey] = $normalizer;
        }

        throw new \RuntimeException(\sprintf('Specification "%s" with context "%s" does not have a supported normalizer registered.', \get_debug_type($specification), \get_debug_type($context)));
    }
}
