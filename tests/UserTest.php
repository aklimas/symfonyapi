<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Container;

class UserTest extends Engine
{
    public function testCreateUser()
    {

        $this->createUserRoleUser();

        // Validate the response
        $this->assertResponseIsSuccessful();

        // Additional assertions or checks for the created user, such as checking the response content or querying the database

        $user = $this->em->getRepository(User::class)->findOneBy(['login' => 'login']);

        // Example: Assert the user exists in the database
        $this->assertNotNull($user);
        $this->assertEquals('FirstName', $user->getFirstName());
        $this->assertEquals('LastName', $user->getLastName());
        $this->assertEquals('test@test.pl', $user->getEmail());

    }
/*
    public function testInvalidCredentials()
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

    public function testVerifyUser(){
        $user = $this->em->getRepository(User::class)->findOneBy(['login' => 'login']);
        $user->setIsVerified(true);
        $this->em->getRepository(User::class)->save($user,true);
        $this->assertEquals(true, true);
    }

     public function testSuccessfulLogin()
     {
         $client = self::createClient();

         // Send a login request with valid credentials
         $response = $client->request('POST', '/auth', [
             'headers' => ['Content-Type' => 'application/json'],
             'json' => [
                 'email' => 'test@test.pl',
                 'password' => 'AAbbcc1133**',
             ],
         ]);

         $this->assertResponseStatusCodeSame(200);
         $json = $response->toArray();
         $this->assertArrayHasKey('token', $json);
     }*/
}
