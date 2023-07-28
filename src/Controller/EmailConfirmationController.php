<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
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
        try {
            $decodedToken = JWTKey::decode($request->query->get('token'));
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
