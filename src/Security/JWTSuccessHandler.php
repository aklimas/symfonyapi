<?php

namespace App\Security;


use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class JWTSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private JWTTokenManagerInterface $jwtManager;

    public function __construct(JWTTokenManagerInterface $jwtManager, private readonly UserRepository $userRepository)
    {
        $this->jwtManager = $jwtManager;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token) :Response
    {
        $user = $this->userRepository->find($token->getUser());

        if($user->isVerified() === false){

            $responseData = [
                'code' => 401,
                'message' => 'Invalid credentials.',
            ];

            return new Response(json_encode($responseData), Response::HTTP_UNAUTHORIZED, [
                'Content-Type' => 'application/json',
            ]);
        }

        return new Response(json_encode(['token' => $this->jwtManager->create($user)]), 200, [
            'Content-Type' => 'application/json',
        ]);
    }
}
