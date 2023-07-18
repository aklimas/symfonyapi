<?php

namespace App\State\Country\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class Visit extends AbstractController implements ProcessorInterface
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): JsonResponse
    {

        $data->addUser($this->userRepository->find($this->getUser()));

        $this->userRepository->save($data,true);

        return new JsonResponse("Country visit ({$data->getName()}) has been added");
    }
}
