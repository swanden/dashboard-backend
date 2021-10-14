<?php

namespace App\Tests\Functional\Users;

use App\Security\UserProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ShowTest extends WebTestCase
{
    private const URI = '/users';

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

        self::assertArrayHasKey('id', $data[0]);
        self::assertNotEmpty($data[0]['id']);

        self::assertArrayHasKey('firstname', $data[0]);
        self::assertNotEmpty($data[0]['firstname']);

        self::assertArrayHasKey('lastname', $data[0]);
        self::assertNotEmpty($data[0]['lastname']);

        self::assertArrayHasKey('date', $data[0]);
        self::assertNotEmpty($data[0]['date']);

        self::assertArrayHasKey('email', $data[0]);
        self::assertNotEmpty($data[0]['email']);

        self::assertArrayHasKey('role', $data[0]);
        self::assertNotEmpty($data[0]['role']);

        self::assertArrayHasKey('status', $data[0]);
        self::assertNotEmpty($data[0]['status']);
    }

    public function testIsNotGranted(): void
    {
        $client = static::createClient();
        $userProvider = static::getContainer()->get(UserProvider::class);
        $admin = $userProvider->loadUserByUsername('test-user@example.com');

        $client->loginUser($admin);

        $client->request('GET', self::URI);

        self::assertEquals(403, $client->getResponse()->getStatusCode());
    }
}