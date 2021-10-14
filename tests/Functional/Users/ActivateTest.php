<?php

namespace App\Tests\Functional\Users;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Tests\Functional\UserProvider;

final class ActivateTest extends WebTestCase
{
    private const URI = '/users/activate';

    public function testPost(): void
    {
        $client = static::createClient();
        $userProvider = static::getContainer()->get(UserProvider::class);
        $admin = $userProvider->loadUserByUsername('test-admin@example.com');

        $client->loginUser($admin);

        $client->request('GET', self::URI);

        self::assertEquals(405, $client->getResponse()->getStatusCode());
    }

    public function testSuccess(): void
    {
        $client = static::createClient();
        $userProvider = static::getContainer()->get(UserProvider::class);
        $admin = $userProvider->loadUserByUsername('test-admin@example.com');
        $user = $userProvider->loadUserByUsername('blocked-user@example.com');

        $client->loginUser($admin);

        $client->request('POST', self::URI, [], [], ['Content-Type' => 'application/json'], json_encode([
            'id' => $user->getId()
        ]));

        self::assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testNoIdInRequest(): void
    {
        $client = static::createClient();
        $userProvider = static::getContainer()->get(UserProvider::class);
        $admin = $userProvider->loadUserByUsername('test-admin@example.com');

        $client->loginUser($admin);

        $client->request('POST', self::URI, [], [], ['Content-Type' => 'application/json'], json_encode([
        ]));

        self::assertEquals(400, $client->getResponse()->getStatusCode());

        $content = $client->getResponse()->getContent();
        $data = json_decode($content);

        self::assertIsObject($data);
        self::assertObjectHasAttribute('errors', $data);
        self::assertIsArray($data->errors);

        self::assertEquals('id is required.', $data->errors[0]);
    }

    public function testUserIsAlreadyActive(): void
    {
        $client = static::createClient();
        $userProvider = static::getContainer()->get(UserProvider::class);
        $admin = $userProvider->loadUserByUsername('test-admin@example.com');
        $user = $userProvider->loadUserByUsername('test-user@example.com');

        $client->loginUser($admin);

        $client->request('POST', self::URI, [], [], ['Content-Type' => 'application/json'], json_encode([
            'id' => $user->getId(),
        ]));

        $content = $client->getResponse()->getContent();
        $data = json_decode($content);

        self::assertObjectHasAttribute('error', $data);
        self::assertNotEmpty($data->error);

        self::assertObjectHasAttribute('code', $data->error);
        self::assertEquals(422, $data->error->code);

        self::assertObjectHasAttribute('message', $data->error);
        self::assertEquals('User is already active.', $data->error->message);
    }

    public function testUserIsNotFound(): void
    {
        $client = static::createClient();
        $userProvider = static::getContainer()->get(UserProvider::class);
        $admin = $userProvider->loadUserByUsername('test-admin@example.com');

        $client->loginUser($admin);

        $client->request('POST', self::URI, [], [], ['Content-Type' => 'application/json'], json_encode([
            'id' => '00000000-0000-0000-0000-000000000000',
        ]));

        self::assertEquals(422, $client->getResponse()->getStatusCode());

        $content = $client->getResponse()->getContent();
        $data = json_decode($content);

        self::assertObjectHasAttribute('error', $data);
        self::assertNotEmpty($data->error);

        self::assertObjectHasAttribute('code', $data->error);
        self::assertEquals(422, $data->error->code);

        self::assertObjectHasAttribute('message', $data->error);
        self::assertEquals('User is not found.', $data->error->message);
    }

    public function testWrongUuidFormat(): void
    {
        $client = static::createClient();
        $userProvider = static::getContainer()->get(UserProvider::class);
        $admin = $userProvider->loadUserByUsername('test-admin@example.com');

        $client->loginUser($admin);

        $client->request('POST', self::URI, [], [], ['Content-Type' => 'application/json'], json_encode([
            'id' => '0000000-0000-0000-0000-000000000000', // Wrong UUID format
        ]));

        self::assertEquals(400, $client->getResponse()->getStatusCode());
    }

    public function testIsNotGranted(): void
    {
        $client = static::createClient();
        $userProvider = static::getContainer()->get(UserProvider::class);
        $admin = $userProvider->loadUserByUsername('test-user@example.com');
        $user = $userProvider->loadUserByUsername('blocked-user@example.com');

        $client->loginUser($admin);

        $client->request('POST', self::URI, [], [], ['Content-Type' => 'application/json'], json_encode([
            'id' => $user->getId(),
        ]));

        self::assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public function testUnableToEditYourself(): void
    {
        $client = static::createClient();
        $userProvider = static::getContainer()->get(UserProvider::class);
        $admin = $userProvider->loadUserByUsername('test-admin@example.com');

        $client->loginUser($admin);

        $client->request('POST', self::URI, [], [], ['Content-Type' => 'application/json'], json_encode([
            'id' => $admin->getId(),
        ]));

        self::assertEquals(403, $client->getResponse()->getStatusCode());

        $content = $client->getResponse()->getContent();
        $data = json_decode($content);

        self::assertObjectHasAttribute('error', $data);
        self::assertNotEmpty($data->error);

        self::assertObjectHasAttribute('code', $data->error);
        self::assertEquals(403, $data->error->code);

        self::assertObjectHasAttribute('message', $data->error);
        self::assertEquals('Unable to activate yourself.', $data->error->message);
    }
}