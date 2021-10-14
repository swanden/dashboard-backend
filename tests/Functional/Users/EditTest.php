<?php

namespace App\Tests\Functional\Users;

use App\Security\UserProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class EditTest extends WebTestCase
{
    private const URI = '/users/edit';

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
        $user = $userProvider->loadUserByUsername('test-user@example.com');

        $client->loginUser($admin);

        $client->request('POST', self::URI, [], [], ['Content-Type' => 'application/json'], json_encode([
            'id' => $user->getId(),
            'email' => 'test-user@example.com',
            "firstname" => "NewFirstName",
            "lastname" => "NewLastName",
            "role" => "Admin"
        ]));

        self::assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testWrongRole(): void
    {
        $client = static::createClient();
        $userProvider = static::getContainer()->get(UserProvider::class);
        $admin = $userProvider->loadUserByUsername('test-admin@example.com');
        $user = $userProvider->loadUserByUsername('test-user@example.com');

        $client->loginUser($admin);

        $client->request('POST', self::URI, [], [], ['Content-Type' => 'application/json'], json_encode([
            'id' => $user->getId(),
            'email' => 'test-user@example.com',
            "firstname" => "NewFirstName",
            "lastname" => "NewLastName",
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

    public function testUserIsNotFound(): void
    {
        $client = static::createClient();
        $userProvider = static::getContainer()->get(UserProvider::class);
        $admin = $userProvider->loadUserByUsername('test-admin@example.com');

        $client->loginUser($admin);

        $client->request('POST', self::URI, [], [], ['Content-Type' => 'application/json'], json_encode([
            'id' => '00000000-0000-0000-0000-000000000000',
//            'id' => '4deb88e3-2b1c-4b7a-9621-ba54d31a9b74',
            'email' => 'not-existing-user@example.com',
            "firstname" => "NewFirstName",
            "lastname" => "NewLastName",
            "role" => "Admin"
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
            'email' => 'not-existing-user@example.com',
            "firstname" => "NewFirstName",
            "lastname" => "NewLastName",
            "role" => "Admin"
        ]));

        self::assertEquals(400, $client->getResponse()->getStatusCode());
    }

    public function testIsNotGranted(): void
    {
        $client = static::createClient();
        $userProvider = static::getContainer()->get(UserProvider::class);
        $admin = $userProvider->loadUserByUsername('test-user@example.com');
        $user = $userProvider->loadUserByUsername('test-user@example.com');

        $client->loginUser($admin);

        $client->request('POST', self::URI, [], [], ['Content-Type' => 'application/json'], json_encode([
            'id' => $user->getId(),
            'email' => 'not-exists-user@example.com',
            "firstname" => "NewFirstName",
            "lastname" => "NewLastName",
            "role" => "Admin"
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
            'email' => 'test-admin@example.com',
            "firstname" => "NewFirstName",
            "lastname" => "NewLastName",
            "role" => "User"
        ]));

        self::assertEquals(403, $client->getResponse()->getStatusCode());

        $content = $client->getResponse()->getContent();
        $data = json_decode($content);

        self::assertObjectHasAttribute('error', $data);
        self::assertNotEmpty($data->error);

        self::assertObjectHasAttribute('code', $data->error);
        self::assertEquals(403, $data->error->code);

        self::assertObjectHasAttribute('message', $data->error);
        self::assertEquals('Unable to edit yourself.', $data->error->message);
    }
}