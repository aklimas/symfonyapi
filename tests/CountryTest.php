<?php

namespace App\Tests;

use App\Entity\Country;

class CountryTest extends Engine
{

    public function testAddNewCountryWithoutAuth(): void
    {
        $client = self::createClient();

        // Send a login request with valid credentials
        $response = $client->request('POST', '/api/countries', [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => [
                "name" => "Polska",
                "languages" => [
                    ["name" => "Polski"],
                    ["name" => "Angielski"]
                ]
            ]
        ]);
        $this->assertResponseStatusCodeSame(401);
    }

    public function testAddNewCountry(): void
    {
        $client = self::createClient();
        $this->createUserRoleUser();

        $response = $client->request('POST', '/api/countries', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $this->headerUser()
            ],
            'json' => [
                "name" => "Polska",
                "languages" => [
                    ["name" => "Polski"],
                    ["name" => "Angielski"]
                ]
            ]
        ]);
        $this->assertResponseStatusCodeSame(200);
        $country = $this->em->getRepository(Country::class)->findOneBy(['name' => 'Polska']);
        $this->assertEquals('Polska', $country->getName());
    }

    public function testUpdateCountry(): void
    {
        $this->assertTrue(true);
    }

    public function testGetListCountry(): void
    {
        $client = self::createClient();
        $response = $client->request('GET', '/api/countries', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $this->headerUser()
            ]
        ]);
        $this->assertResponseStatusCodeSame(200);

        //var_dump($response->getContent());


        $this->assertTrue(true);
    }

    public function testAcceptedCountryWithRoleUser(): void
    {

        $country = $this->em->getRepository(Country::class)->findOneBy(['name' => 'Polska']);

        $client = self::createClient();
        $response = $client->request('PUT', '/api/countries/' . $country->getId() . '/accept', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $this->headerUser()
            ]
        ]);
        $this->assertResponseStatusCodeSame(200);

        $country = $this->em->getRepository(Country::class)->findOneBy(['name' => 'Polska']);

        $this->assertEquals(false, $country->isVerified());


    }

    public function testVisitCountryWhenIsNotVerified(): void
    {
        $country = $this->em->getRepository(Country::class)->findOneBy(['name' => 'Polska']);
        $client = self::createClient();
        $response = $client->request('PUT', '/api/countries/' . $country->getId() . '/visit', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $this->headerUser()
            ]
        ]);

        //dd($response);
        $this->assertResponseStatusCodeSame(200);

    }

    public function testAcceptedCountryWithRoleAdmin(): void
    {

        $this->createUserRoleAdmin();

        $country = $this->em->getRepository(Country::class)->findOneBy(['name' => 'Polska']);

        $client = self::createClient();
        $response = $client->request('GET', '/api/countries/' . $country->getId() . '/accept', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $this->headerAdmin()
            ]
        ]);

        $this->assertResponseStatusCodeSame(200);

        $country = $this->em->getRepository(Country::class)->findOneBy(['name' => 'Polska']);

        $this->assertEquals(false, $country->isVerified());


        //$this->assertTrue(true);
    }

    public function testVisitCountryWhenIsVerified(): void
    {
        $this->assertTrue(true);
    }

    public function testDeleteCountryWithRoleUser(): void
    {
        $this->createUserRoleUser();

        $country = $this->em->getRepository(Country::class)->findOneBy(['name' => 'Polska']);

        $client = self::createClient();
        $response = $client->request('DELETE', '/api/countries/' . $country->getId(), [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $this->headerUser()
            ]
        ]);


        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testDeleteCountryWithRoleAdmin(): void
    {
        $this->createUserRoleAdmin();

        $country = $this->em->getRepository(Country::class)->findOneBy(['name' => 'Polska']);

        $client = self::createClient();
        $response = $client->request('DELETE', '/api/countries/' . $country->getId(), [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $this->headerAdmin()
            ]
        ]);

        $this->assertResponseStatusCodeSame(200);

    }
}
