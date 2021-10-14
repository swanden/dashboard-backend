<?php

namespace App\Tests\Functional\Users;

use App\Security\UserProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CreateTest extends WebTestCase
{
    private const URI = '/users/create';

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

        $client->loginUser($admin);

        $client->request('POST', self::URI, [], [], ['Content-Type' => 'application/json'], json_encode([
            'email' => 'new-user@example.com',
            "firstname" => "New",
            "lastname" => "User",
            "role" => "User"
        ]));

        self::assertEquals(201, $client->getResponse()->getStatusCode());

        $content = $client->getResponse()->getContent();
        $data = json_decode($content);

        self::assertObjectHasAttribute('id', $data);
        self::assertNotEmpty($data->id);
    }

    public function testWrongRole(): void
    {
        $client = static::createClient();
        $userProvider = static::getContainer()->get(UserProvider::class);
        $admin = $userProvider->loadUserByUsername('test-admin@example.com');

        $client->loginUser($admin);

        $client->request('POST', self::URI, [], [], ['Content-Type' => 'application/json'], json_encode([
            'email' => 'new-user@example.com',
            "firstname" => "New",
            "lastname" => "User",
            "role" => "WrongRole"
        ]));

        self::assertEquals(422, $client->getResponse()->getStatusCode());

        $content = $client->getResponse()->getContent();
        $data = json_decode($content);

        self::assertObjectHasAttribute('error', $data);
        self::assertNotEmpty($data->error);

        self::assertObjectHasAttribute('code', $data->error);
        self::assertEquals(422, $data->error->code);

        self::assertObjectHasAttribute('message', $data->error);
        self::assertEquals('Role WrongRole does not exists.', $data->error->message);
    }

    public function testUserAlreadyExists(): void
    {
        $client = static::createClient();
        $userProvider = static::getContainer()->get(UserProvider::class);
        $admin = $userProvider->loadUserByUsername('test-admin@example.com');

        $client->loginUser($admin);

        $client->request('POST', self::URI, [], [], ['Content-Type' => 'application/json'], json_encode([
            'email' => 'test-user@example.com',
            "firstname" => "New",
            "lastname" => "User",
            "role" => "User"
        ]));

        self::assertEquals(422, $client->getResponse()->getStatusCode());

        $content = $client->getResponse()->getContent();
        $data = json_decode($content);

        self::assertObjectHasAttribute('error', $data);
        self::assertNotEmpty($data->error);

        self::assertObjectHasAttribute('code', $data->error);
        self::assertEquals(422, $data->error->code);

        self::assertObjectHasAttribute('message', $data->error);
        self::assertEquals('User with this email already exists.', $data->error->message);
    }

    public function testIsNotGranted(): void
    {
        $client = static::createClient();
        $userProvider = static::getContainer()->get(UserProvider::class);
        $admin = $userProvider->loadUserByUsername('test-user@example.com');

        $client->loginUser($admin);

        $client->request('POST', self::URI, [], [], ['Content-Type' => 'application/json'], json_encode([
            'email' => 'new-user@example.com',
            "firstname" => "New",
            "lastname" => "User",
            "role" => "User"
        ]));

        self::assertEquals(403, $client->getResponse()->getStatusCode());
    }
}