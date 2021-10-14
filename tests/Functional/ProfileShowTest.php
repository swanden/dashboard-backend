<?php

namespace App\Tests\Functional;

use App\Security\UserProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ProfileShowTest extends WebTestCase
{
    private const URI = '/profile';

    public function testPost(): void
    {
        $client = static::createClient();
        $userProvider = static::getContainer()->get(UserProvider::class);
        $admin = $userProvider->loadUserByUsername('test-admin@example.com');

        $client->loginUser($admin);

        $client->request('POST', self::URI);

        self::assertEquals(405, $client->getResponse()->getStatusCode());
    }

    public function testSuccess(): void
    {
        $client = static::createClient();
        $userProvider = static::getContainer()->get(UserProvider::class);
        $admin = $userProvider->loadUserByUsername('test-admin@example.com');

        $client->loginUser($admin);

        $client->request('GET', self::URI);

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        self::assertJson($content = $client->getResponse()->getContent());

        $data = json_decode($content, true);

        self::assertIsArray($data);

        self::assertArrayHasKey('first', $data);
        self::assertEquals('First', $data['first']);

        self::assertArrayHasKey('last', $data);
        self::assertEquals('Last', $data['last']);

        self::assertArrayHasKey('email', $data);
        self::assertEquals('test-admin@example.com', $data['email']);

        self::assertArrayHasKey('created', $data);
        self::assertNotEmpty($data['last']);

        self::assertArrayHasKey('role', $data);
        self::assertEquals('Admin', $data['role']);

        self::assertArrayHasKey('status', $data);
        self::assertEquals('Active', $data['status']);
    }
}