<?php

namespace App\State\Country\Provider;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\CountryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CountryCollectionStateProvider extends AbstractController implements ProviderInterface
{
    public function __construct(
        private readonly CountryRepository $countryRepository
    )
    {
    }
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        // TODO Implement DTO

        if($operation instanceof GetCollection){
            if ($this->isGranted('ROLE_ADMIN')) {
                return $this->countryRepository->findAll();
            } else {
                return $this->countryRepository->findBy(['verified' => true]);
            }
        }
        return null;

    }
}
