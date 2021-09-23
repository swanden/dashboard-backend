<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Security\UserProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class LoginTest extends WebTestCase
{
    public function testVisitingWhileLoggedIn()
    {
        $client = static::createClient([], [
            'Accept' => 'application/json'
        ]);
        $userRepository = static::getContainer()->get(UserProvider::class);

        $testUser = $userRepository->loadUserByUsername('admin@example.com');

        $client->loginUser($testUser);

        $client->request('GET', '/api/profile');

        $this->assertResponseIsSuccessful();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertJson($content = $client->getResponse()->getContent());
    }
}