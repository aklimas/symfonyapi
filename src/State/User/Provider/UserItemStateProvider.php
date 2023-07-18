<?php

namespace App\State\User\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Service\DateService;

class UserItemStateProvider implements ProviderInterface
{

    public function __construct(
        private readonly ProviderInterface $itemProvider,
        private readonly DateService $dateService
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $user = $this->itemProvider->provide($operation, $uriVariables, $context);

        // TODO Implement Dto

        $user->setAge($this->dateService->calculateAge($user->getDateBirthday()->format('Y-m-d')));

        return $user;

    }
}
