<?php

namespace App\State\User\Provider;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserCollectionStateProvider extends AbstractController implements ProviderInterface
{
    public function __construct(
        private readonly UserRepository $userRepository
    )
    {
    }
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        // TODO Implement DTO

        if($operation instanceof GetCollection){
            if ($this->isGranted('ROLE_ADMIN')) {
                return $this->userRepository->findAll();
            } else {
                return $this->userRepository->findBy(['isVerified' => true,'softDelete' => null]);
            }
        }
        return null;

    }
}
