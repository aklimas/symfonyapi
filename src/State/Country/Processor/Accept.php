<?php

namespace App\State\Country\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class Accept implements ProcessorInterface
{

    public function __construct(private readonly ProcessorInterface $processor)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): JsonResponse
    {
        $data->setVerified(true);
        return $this->processor->process($data, $operation, $uriVariables, $context);
    }
}
