<?php

namespace App\Serializer;

use App\Entity\Language;
use App\Repository\LanguageRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class CountryDenormalizer implements DenormalizerInterface
{
    public function __construct(
        private readonly DenormalizerInterface $denormalizer,
        private readonly LanguageRepository $languageRepository,
        private readonly Security $security)
    {
    }

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            if (isset($data['users'])) {
                unset($data['users']);
            }
        }

        if (isset($data['languages'])) {
            foreach ($data['languages'] as $key => $language) {
                $d = new Language();
                $d->setName($language['name']);
                $this->languageRepository->save($d, true);
                $data['languages'][$key] = '/api/languages/'.$d->getId();
            }
        }

        return $this->denormalizer->denormalize($data, $type);
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return 'App\Entity\Country' === $type;
    }
}
