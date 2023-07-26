<?php

namespace App\State\Country\Provider;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\CountryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class CountryCollectionStateProvider extends AbstractController implements ProviderInterface
{
    public function __construct(
        private readonly CountryRepository $countryRepository
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $countryCollection = 0;

        if ($operation instanceof GetCollection) {
            $countryCollection = ($this->isGranted('ROLE_ADMIN')) ?
                $this->countryRepository->findAll() :
                $this->countryRepository->findBy(['verified' => true]);
        }

        if (count($countryCollection) > 0) {
            return $countryCollection;
        }

        return new Response('Not found countries', 200);
    }
}
