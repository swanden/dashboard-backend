<?php

declare(strict_types=1);

namespace App\Tests\Functional\Auth;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SignUpTest extends WebTestCase
{
    private const URI = '/auth/signup';

    public function testGet(): void
    {
        $client = static::createClient();
        $client->request('GET', self::URI);

        self::assertEquals(405, $client->getResponse()->getStatusCode());
    }

    public function testSuccess(): void
    {
        $client = static::createClient();
        $client->request('POST', self::URI, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'new-user@example.com',
            'password' => 'password',
        ]));

        self::assertEquals(201, $client->getResponse()->getStatusCode());
        self::assertJson($content = $client->getResponse()->getContent());

        $data = json_decode($content, true);

        self::assertEquals([], $data);

        self::assertEmailCount(1);
    }

    public function testNotEnoughEmailWithoutPassword(): void
    {
        $client = static::createClient();
        $client->request('POST', self::URI, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'not-email'
        ]));

        self::assertEquals(400, $client->getResponse()->getStatusCode());
        self::assertJson($content = $client->getResponse()->getContent());

        $data = json_decode($content, true);

        self::assertArrayHasKey('errors', $data);
        self::assertNotEmpty($data['errors']);

        self::assertCount(1, $data['errors']);
        self::assertEquals('password is required.', $data['errors'][0]);
    }

    public function testNotValid(): void
    {
        $client = static::createClient();
        $client->request('POST', self::URI, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'not-email',
            'password' => 'short',
        ]));

        self::assertEquals(400, $client->getResponse()->getStatusCode());
        self::assertJson($content = $client->getResponse()->getContent());

        $expected = '{"type":"https:\/\/symfony.com\/errors\/validation","title":"Validation Failed","detail":"email: This value is not a valid email address.\npassword: This value is too short. It should have 6 characters or more.","violations":[{"propertyPath":"email","title":"This value is not a valid email address.","parameters":{"{{ value }}":"\"not-email\""},"type":"urn:uuid:bd79c0ab-ddba-46cc-a703-a7a4b08de310"},{"propertyPath":"password","title":"This value is too short. It should have 6 characters or more.","parameters":{"{{ value }}":"\"short\"","{{ limit }}":"6"},"type":"urn:uuid:9ff3fdc4-b214-49db-8718-39c315e33d45"}]}';
        self::assertJsonStringEqualsJsonString($expected, $content);

        $data = json_decode($content, true);

        self::assertArrayHasKey('violations', $data);
        self::assertNotEmpty($data['violations']);

        self::assertCount(2, $data['violations']);

        self::assertArrayHasKey('propertyPath', $data['violations'][0]);
        self::assertEquals('email', $data['violations'][0]['propertyPath']);

        self::assertArrayHasKey('propertyPath', $data['violations'][1]);
        self::assertEquals('password', $data['violations'][1]['propertyPath']);
    }

    public function testExists(): void
    {
        $client = static::createClient();
        $client->request('POST', self::URI, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'test-user@example.com',
            'password' => 'password',
        ]));

        self::assertEquals(422, $client->getResponse()->getStatusCode());
        self::assertJson($content = $client->getResponse()->getContent());

        $data = json_decode($content, true);

        self::assertArrayHasKey('error', $data);
        self::assertNotEmpty($data['error']);

        self::assertArrayHasKey('code', $data['error']);
        self::assertEquals(422, $data['error']['code']);

        self::assertArrayHasKey('message', $data['error']);
        self::assertEquals('User already exists.', $data['error']['message']);
    }
}