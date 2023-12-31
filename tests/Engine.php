<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class Engine extends ApiTestCase
{
    protected ?EntityManagerInterface $em;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->em = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        // echo "ffsdfsd";
    }

    public function createUserRoleUser(): ?ResponseInterface
    {
        $response = null;

        /** @var UserRepository $userRepository */
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->findOneBy(['login' => 'userLogin']);
        if (!$user) {
            $client = static::createClient();
            $response = $client->request('POST', '/api/users', [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => [
                    'login' => 'userLogin',
                    'email' => 'user@user.pl',
                    'password' => 'AAbbcc1133**',
                    'firstName' => 'FirstName',
                    'lastName' => 'LastName',
                    'dateBirthday' => '1987-01-01T15:53:44.445Z',
                ],
            ]);
        }

        $user = $userRepository->findOneBy(['login' => 'userLogin']);
        $user->setIsVerified(true);
        $userRepository->save($user, true);

        return $response;
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function createUserRoleAdmin(): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->em->getRepository(User::class);
        $adminUser = $userRepository->findOneBy(['login' => 'adminLogin']);
        if (!$adminUser) {
            $client = static::createClient();
            $client->request('POST', '/api/users', [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => [
                    'login' => 'adminLogin',
                    'email' => 'admin@admin.pl',
                    'password' => 'AAbbcc1133**',
                    'firstName' => 'FirstNameAdmin',
                    'lastName' => 'LastNameAdmin',
                    'dateBirthday' => '1987-01-01T15:53:44.445Z',
                ],
            ]);
        }

        $adminUser = $userRepository->findOneBy(['login' => 'adminLogin']);
        $adminUser->setIsVerified(true);
        $adminUser->setRoles(['ROLE_ADMIN']);
        $userRepository->save($adminUser, true);
    }

    protected function clearDB(): void
    {
        $this->em->createQuery(
            "DELETE FROM App\Entity\User e"
        )->execute();
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    protected function headerUser(): string
    {
        $client = self::createClient();

        // Send a login request with valid credentials
        $response = $client->request('POST', '/auth', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'email' => 'user@user.pl',
                'password' => 'AAbbcc1133**',
            ],
        ]);

        $array = $response->toArray();

        return "Bearer {$array['token']}";
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    protected function headerAdmin(): string
    {
        $client = self::createClient();

        // Send a login request with valid credentials
        $response = $client->request('POST', '/auth', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'email' => 'admin@admin.pl',
                'password' => 'AAbbcc1133**',
            ],
        ]);

        $array = $response->toArray();

        return "Bearer {$array['token']}";
    }
}
