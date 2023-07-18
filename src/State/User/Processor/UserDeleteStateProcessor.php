<?php

namespace App\State\User\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;

class UserDeleteStateProcessor implements ProcessorInterface
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if($data instanceof User){
            $data->setSoftDelete(true);
            $this->userRepository->save($data, true);
            return new Response('User Deleted', 200);
        }
        return null;

    }
}
