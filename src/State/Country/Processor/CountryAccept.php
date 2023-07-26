<?php

namespace App\State\Country\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Country;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class CountryAccept extends AbstractController implements ProcessorInterface
{
    public function __construct(private readonly ProcessorInterface $processor)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): JsonResponse|Country
    {
        if (!$data instanceof Country) {
            return $this->json([
                'message' => 'Not found country',
                'code' => 200,
            ]);
        }
        if ($this->isGranted('ROLE_ADMIN')) {
            $data->setVerified(true);
        }

        return $this->processor->process($data, $operation, $uriVariables, $context);
    }
}
