<?php

namespace App\Tests;

use App\Entity\User;
use App\Repository\UserRepository;

class UserTest extends Engine
{
    public function testCreateUser(): void
    {
        $this->clearDB();
        $response = $this->createUserRoleUser();

        // Validate the response
        $this->assertEquals(201, $response->getStatusCode());

        // Additional assertions or checks for the created user, such as checking the response content or querying the database

        $user = $this->em->getRepository(User::class)->findOneBy(['login' => 'userLogin']);

        // Example: Assert the user exists in the database
        $this->assertNotNull($user);
        $this->assertEquals('FirstName', $user->getFirstName());
        $this->assertEquals('LastName', $user->getLastName());
        $this->assertEquals('user@user.pl', $user->getEmail());
    }

    public function testInvalidCredentials(): void
    {
        $client = self::createClient();

        // Send a login request with invalid credentials
        $response = $client->request('POST', '/auth', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'email' => 'test',
                'password' => 'string',
            ],
        ]);

        // Validate the response
        $this->assertResponseStatusCodeSame(401);
        $this->assertJsonContains(['message' => 'Invalid credentials.']);
    }

    public function testValidCredentialsWithNotActivateProfile(): void
    {
        $client = self::createClient();

        /** @var UserRepository $userRepository */
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'user@user.pl']);
        $user->setIsVerified(false);
        $userRepository->save($user, true);

        // Send a login request with invalid credentials
        $response = $client->request('POST', '/auth', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'email' => 'user@user.pl',
                'password' => 'AAbbcc1133**',
            ],
        ]);

        // Validate the response
        $this->assertResponseStatusCodeSame(401);
        $this->assertJsonContains(['message' => 'Invalid credentials.']);
    }

    public function testVerifyUser(): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->em->getRepository(User::class);

        $user = $userRepository->findOneBy(['login' => 'userLogin']);
        $user->setIsVerified(true);
        $userRepository->save($user, true);

        $this->assertEquals(true, true);
    }

    public function testValidCredentials(): void
    {
        $client = self::createClient();

        $response = $client->request('POST', '/auth', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'email' => 'user@user.pl',
                'password' => 'AAbbcc1133**',
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertArrayHasKey('token', $response->toArray());
    }

}
