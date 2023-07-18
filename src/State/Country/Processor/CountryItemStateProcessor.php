<?php

namespace App\State\Country\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;

class CountryItemStateProcessor implements ProcessorInterface
{
    public function __construct(private readonly ProcessorInterface $processor)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {

        return $this->processor->process($data, $operation, $uriVariables, $context);
    }
}
