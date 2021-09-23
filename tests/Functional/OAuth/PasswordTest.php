<?php

declare(strict_types=1);

namespace App\Tests\Functional\OAuth;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PasswordTest extends WebTestCase
{
    private const URI = '/token';

    public function testMethod(): void
    {
        $client = static::createClient();
        $client->request('GET', self::URI);
        self::assertEquals(405, $client->getResponse()->getStatusCode());
    }

    public function testSuccess(): void
    {
        $client = static::createClient();
        $client->request('POST', self::URI, [
            'grant_type' => 'password',
            'username' => 'oauth-password-user@example.com',
            'password' => 'password',
            'client_id' => 'oauth',
            'client_secret' => 'secret',
            'access_type' => 'offline',
        ]);

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        self::assertJson($content = $client->getResponse()->getContent());

        $data = json_decode($content, true);

        self::assertArrayHasKey('token_type', $data);
        self::assertEquals('Bearer', $data['token_type']);

        self::assertArrayHasKey('expires_in', $data);
        self::assertNotEmpty($data['expires_in']);

        self::assertArrayHasKey('access_token', $data);
        self::assertNotEmpty($data['access_token']);

        self::assertArrayHasKey('refresh_token', $data);
        self::assertNotEmpty($data['refresh_token']);
    }

    public function testInvalid(): void
    {
        $client = static::createClient();
        $client->request('POST', self::URI, [
            'grant_type' => 'password',
            'username' => 'oauth-password-user@example.com',
            'password' => 'invalid',
            'client_id' => 'oauth',
            'client_secret' => 'secret',
        ]);

        self::assertEquals(400, $client->getResponse()->getStatusCode());
    }
}
