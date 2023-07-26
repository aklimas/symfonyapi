<?php

namespace App\Serializer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class CountryDenormalizer implements DenormalizerInterface
{
    public function denormalize($data, $type, $format = null, array $context = [])
    {

        //dd($data);

        // Usunięcie klucza "users" z danych wejściowych
        if (isset($data['users'])) {
            unset($data['users']);
        }

        return $data;
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        // Wspieramy tylko format JSON i odpowiedni typ danych (np. "App\Entity\SomeEntity")
        //return $format === 'json' && $type === 'App\Entity\Country';
        return $type === 'App\Entity\Country';
    }
}
