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
        if (!$normalizer = $this->getNormalizer($specification, $context)) {
            throw new \RuntimeException(\sprintf('Specification "%s" with context "%s" does not have a supported normalizer registered.', \get_debug_type($specification), \get_debug_type($context)));
        }

        return $normalizer->normalize($specification, $context);
    }

    public function supports(mixed $specification, mixed $context): bool
    {
        return null !== $this->getNormalizer($specification, $context);
    }

    private function getNormalizer(mixed $specification, mixed $context): ?Normalizer
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

        return null;
    }
}
