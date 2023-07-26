<?php

namespace App\Serializer;

use App\Entity\Country;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CountryDataNormalizer implements NormalizerInterface
{
    public function __construct(private readonly NormalizerInterface $normalizer, private readonly Security $security)
    {
    }

    public function normalize($entity, string $format = null, array $context = []): float|int|bool|\ArrayObject|array|string|null
    {

        $data = $this->normalizer->normalize($entity, $format, $context);

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return $data;
        }

        if (isset($data['verified'])) {
            unset($data['verified']);
        }

        if ($this->security->isGranted('ROLE_USER')) {

            return $data;
        }

        if (isset($data['users'])) {
            unset($data['users']);
        }

        return $data;
    }

    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof Country;
    }
}
