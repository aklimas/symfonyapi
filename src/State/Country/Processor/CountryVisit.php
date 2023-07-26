<?php

namespace App\State\Country\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Country;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class CountryVisit extends AbstractController implements ProcessorInterface
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly ProcessorInterface $processor
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): JsonResponse|Country
    {
        if (!$data instanceof Country) {
            return $this->json([
                'error' => 'Not found country',
            ], 410);
        }

        if (true !== $data->isVerified()) {
            return $this->json([
                'error' => 'Not found countries',
            ], 410);
        }

        $data->addUser($this->userRepository->find($this->getUser()));

        return $this->processor->process($data, $operation, $uriVariables, $context);
    }
}
