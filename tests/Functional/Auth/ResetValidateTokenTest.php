<?php

namespace App\Tests\Functional\Auth;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Mime\Email;

final class ResetValidateTokenTest extends WebTestCase
{
    private const URI = '/auth/reset/validate_token';

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

        $client->request('GET', self::URI  . "/$token", [], [], ['Content-Type' => 'application/json']);

        self::assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testWrongToken()
    {
        $client = static::createClient();
        $client->enableProfiler();
        $client->request('POST', '/auth/reset/request', [], [], ['Content-Type' => 'application/json'], json_encode([
            'email' => 'test-user@example.com',
        ]));

        $client->request('GET', self::URI . '/wrong-token', [], [], ['Content-Type' => 'application/json']);

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