<?php

namespace App\State\Country\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class Accept implements ProcessorInterface
{

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): JsonResponse
    {
        $data->setVerified(true);
        return new JsonResponse("Country ({$data->getName()}) has been accepted");
    }
}
