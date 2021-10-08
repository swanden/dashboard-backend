<?php

namespace App\Tests\Functional\Auth;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ResetRequestTest extends WebTestCase
{
    private const URI = '/auth/reset/request';

    public function testGet(): void
    {
        $client = static::createClient();
        $client->request('GET', self::URI);

        self::assertEquals(405, $client->getResponse()->getStatusCode());
    }

    public function testNoDataPassed(): void
    {
        $client = static::createClient();
        $client->request('POST', self::URI);

        self::assertEquals(400, $client->getResponse()->getStatusCode());

        $content = $client->getResponse()->getContent();
        $data = json_decode($content);

        self::assertObjectHasAttribute('errors', $data);
        self::assertNotEmpty($data->errors);
    }

    public function testSuccess()
    {
        $client = static::createClient();
        $client->enableProfiler();
        $client->request('POST', self::URI, [], [], ['Content-Type' => 'application/json'], json_encode([
            'email' => 'test-user@example.com',
        ]));

        self::assertEquals(201, $client->getResponse()->getStatusCode());

        self::assertEmailCount(1);
    }

    public function testWrongEmail()
    {
        $client = static::createClient();
        $client->enableProfiler();
        $client->request('POST', self::URI, [], [], ['Content-Type' => 'application/json'], json_encode([
            'email' => 'wrong_email@example.com',
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

    public function testResetAlreadyRequested()
    {
        $client = static::createClient();
        $client->enableProfiler();
        $client->request('POST', self::URI, [], [], ['Content-Type' => 'application/json'], json_encode([
            'email' => 'test-user@example.com',
        ]));

        self::assertEquals(201, $client->getResponse()->getStatusCode());

        self::assertEmailCount(1);

        $client->request('POST', self::URI, [], [], ['Content-Type' => 'application/json'], json_encode([
            'email' => 'test-user@example.com',
        ]));

        self::assertEquals(422, $client->getResponse()->getStatusCode());

        $content = $client->getResponse()->getContent();
        $data = json_decode($content);

        self::assertObjectHasAttribute('error', $data);
        self::assertNotEmpty($data->error);

        self::assertObjectHasAttribute('code', $data->error);
        self::assertEquals(422, $data->error->code);

        self::assertObjectHasAttribute('message', $data->error);
        self::assertEquals('Password reset has already been requested.', $data->error->message);
    }

}