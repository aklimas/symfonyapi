<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Repository\UserRepository;
use Symfony\Component\DependencyInjection\Container;

class UserTest extends ApiTestCase
{
    private UserRepository $userRepository;

    public function test__construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function testCreateUser()
    {
        $client = static::createClient();
        $container = new Container();

        // Send a request to create a new user
        $client->request('POST', '/api/users', [
            'json' => [
                'login' => 'login',
                'email' => 'klimas.adam@gmail.com',
                'password' => 'AAbbcc1133**',
                'firstName' => 'Adam',
                'lastName' => 'Klimas',
                'dateBirthday' => '1987-06-16'
            ],
        ]);

        // Validate the response
        //$this->assertResponseIsSuccessful();

        // Additional assertions or checks for the created user, such as checking the response content or querying the database

        // Example: Assert the user exists in the database
        $user = $this->userRepository->findOneBy(['username' => 'login']);
        $this->assertNotNull($user);
        $this->assertEquals('John Doe', $user->getFirstName());
        $this->assertEquals('johndoe@example.com', $user->getEmail());
    }

   /* public function testSuccessfulLogin()
    {
        $client = self::createClient();

        // Send a login request with valid credentials
        $response = $client->request('POST', '/auth', [
            'json' => [
                'email' => 'klimas.adam@gmail.com',
                'password' => 'string',
            ],
        ]);

        // Validate the response
        $this->assertResponseIsSuccessful();
        $this->assertJson($response->getContent());

        // Additional assertions for the returned data, such as checking for tokens or user information
    }

    public function testInvalidCredentials()
    {
        $client = self::createClient();

        // Send a login request with invalid credentials
        $response = $client->request('POST', '/auth', [
            'json' => [
                'email' => 'test',
                'password' => 'string',
            ],
        ]);

        // Validate the response
        $this->assertResponseStatusCodeSame(401);
        $this->assertJsonContains(['message' => 'Invalid credentials.']);
    }*/


}
