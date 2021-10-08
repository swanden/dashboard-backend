<?php

namespace App\Tests\Functional\Auth;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Mime\Email;

final class SignUpConfirmTest extends WebTestCase
{
    private const URI = '/auth/signup/confirm';

    public function testGet(): void
    {
        $client = static::createClient();
        $client->request('GET', self::URI);

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
        $client->request('POST', '/auth/signup', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'new-user@example.com',
            'password' => 'password',
        ]));

        self::assertEquals(201, $client->getResponse()->getStatusCode());
        self::assertEmailCount(1);

        $mailCollector = $client->getProfile()->getCollector('mailer');
        /* @var array<Email> */
        $htmlMessages = $mailCollector->getEvents()->getMessages();
        $mailBody = $htmlMessages[0]->getTextBody();
        preg_match('/(token=)(.*?)(\n)/', $mailBody, $matches);
        $token = $matches[2];

        $client->request('GET', self::URI, ['token' => $token], [], ['CONTENT_TYPE' => 'application/json']);

        self::assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testFailed()
    {
        $client = static::createClient();
        $client->enableProfiler();
        $client->request('POST', '/auth/signup', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'new-user@example.com',
            'password' => 'password',
        ]));

        self::assertEquals(201, $client->getResponse()->getStatusCode());
        self::assertEmailCount(1);

        $token = 'wrong-token';
        $client->request('GET', self::URI, ['token' => $token], [], ['CONTENT_TYPE' => 'application/json']);

        self::assertEquals(422, $client->getResponse()->getStatusCode());

        $content = $client->getResponse()->getContent();
        $data = json_decode($content);

        self::assertObjectHasAttribute('error', $data);
        self::assertNotEmpty($data->error);

        self::assertObjectHasAttribute('code', $data->error);
        self::assertEquals(422, $data->error->code);

        self::assertObjectHasAttribute('message', $data->error);
        self::assertEquals('Wrong or confirmed token.', $data->error->message);
    }
}
