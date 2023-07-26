<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EmailConfirmationController extends AbstractController
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    /**
     * @Route("/email/confirm", name="email_confirm", methods={"GET"})
     */
    public function confirm(Request $request): Response
    {
        $token = $request->query->get('token');

        try {
            $headers = new \stdClass();
            // TODO wyciągnąć klucz do ENV
            $decodedToken = JWT::decode($token, new Key('12345', 'HS256'), $headers);
        } catch (\Exception $e) {
            return new Response('Email confirmed failed', 404);
        }

        $user = $this->userRepository->find($decodedToken->userId);

        if (!$user instanceof User) {
            return new Response('Email confirmed failed', 404);
        }

        $user->setIsVerified(true);
        $this->userRepository->save($user, true);

        return new Response('Email confirmed successfully');
    }
}
