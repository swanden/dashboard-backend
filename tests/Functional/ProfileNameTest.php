<?php

namespace App\Tests\Functional;

use App\Security\UserProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ProfileNameTest extends WebTestCase
{
    private const URI = '/profile/name';

    public function testGet(): void
    {
        $client = static::createClient();
        $client->request('GET', self::URI);

        self::assertEquals(405, $client->getResponse()->getStatusCode());
    }

    public function testChangeNameSuccess(): void
    {
        $client = static::createClient();
        $userProvider = static::getContainer()->get(UserProvider::class);

        $testUser = $userProvider->loadUserByUsername('test-user@example.com');

        $client->loginUser($testUser);
        $client->request('POST', self::URI, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'first' => 'John',
            'last' => 'Doe',
        ]));

        self::assertEquals(200, $client->getResponse()->getStatusCode());
        self::assertJson($content = $client->getResponse()->getContent());

        $data = json_decode($content, true);

        self::assertEquals([], $data);
    }

    public function testChangeNameFailed(): void
    {
        $client = static::createClient();
        $userProvider = static::getContainer()->get(UserProvider::class);

        $testUser = $userProvider->loadUserByUsername('test-user@example.com');

        $client->loginUser($testUser);
        $client->request('POST', self::URI, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'first' => 'John'
        ]));

        self::assertEquals(400, $client->getResponse()->getStatusCode());
        self::assertJson($content = $client->getResponse()->getContent());

        $data = json_decode($content, true);

        self::assertArrayHasKey('errors', $data);
        self::assertNotEmpty($data['errors']);

        self::assertCount(1, $data['errors']);
        self::assertEquals('last is required.', $data['errors'][0]);
    }
}