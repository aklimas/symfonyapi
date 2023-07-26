<?php

namespace App\Serializer;

use App\Entity\User;
use App\Service\DateService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UserDataNormalizer implements NormalizerInterface
{
    public function __construct(
        private readonly NormalizerInterface $normalizer,
        private readonly Security            $security,
        private readonly DateService         $dateService
    )
    {
    }

    public function normalize($entity, string $format = null, array $context = []): float|int|bool|\ArrayObject|array|string|null
    {
        $data = $this->normalizer->normalize($entity, $format, $context);


        $data['age'] = $this->dateService->calculateAge($data['dateBirthday']);


        if ($this->security->isGranted('ROLE_ADMIN')) {
            return $data;
        }

        if ($this->security->isGranted('ROLE_USER')) {

            if (isset($data['email'])) {
                unset($data['email']);
            }

            if (isset($data['isVerified'])) {
                unset($data['isVerified']);
            }

            if (isset($data['dateBirthday'])) {
                unset($data['dateBirthday']);
            }

            return $data;
        }

        if (isset($data['users'])) {
            unset($data['users']);
        }


        return $data;
    }

    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof User;
    }
}
