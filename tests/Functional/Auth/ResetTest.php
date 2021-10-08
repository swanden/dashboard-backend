<?php

namespace App\Tests\Functional\Auth;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Mime\Email;

final class ResetTest extends WebTestCase
{
    private const URI = '/auth/reset';

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
        $client->request('POST', '/auth/reset/request', [], [], ['Content-Type' => 'application/json'], json_encode([
            'email' => 'test-user@example.com',
        ]));

        self::assertEquals(201, $client->getResponse()->getStatusCode());

        self::assertEmailCount(1);

        $mailCollector = $client->getProfile()->getCollector('mailer');
        /* @var array<Email> */
        $htmlMessages = $mailCollector->getEvents()->getMessages();
        $mailBody = $htmlMessages[0]->getTextBody();
        preg_match('/(reset\/)(.*?)(\n)/', $mailBody, $matches);
        $token = $matches[2];

        $client->request('POST', self::URI, [], [], ['Content-Type' => 'application/json'], json_encode([
            'token' => $token,
            'password' => 'new-password'
        ]));

        self::assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testWrongToken()
    {
        $client = static::createClient();
        $client->enableProfiler();
        $client->request('POST', '/auth/reset/request', [], [], ['Content-Type' => 'application/json'], json_encode([
            'email' => 'test-user@example.com',
        ]));

        $client->request('POST', self::URI, [], [], ['Content-Type' => 'application/json'], json_encode([
            'token' => 'wrong-token',
            'password' => 'new-password'
        ]));

        self::assertEquals(422, $client->getResponse()->getStatusCode());

        $content = $client->getResponse()->getContent();
        $data = json_decode($content);

        self::assertObjectHasAttribute('error', $data);
        self::assertNotEmpty($data->error);

        self::assertObjectHasAttribute('code', $data->error);
        self::assertEquals(422, $data->error->code);

        self::assertObjectHasAttribute('message', $data->error);
        self::assertEquals('Incorrect or already confirmed token.', $data->error->message);
    }
}