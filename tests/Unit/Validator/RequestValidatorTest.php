<?php

namespace App\Tests\Unit\Validator;

use App\Validator\RequestType;
use App\Validator\RequestValidator;
use PHPUnit\Framework\TestCase;
use App\Model\User\UseCase\SignUp\Request\Command;
use Symfony\Component\HttpFoundation\Request;

final class RequestValidatorTest extends TestCase
{
    public function testJsonBodyRequestSuccess(): void
    {
        $requestBody = '{
            "email": "user@example.com",
            "password": "password"
        }';
        $request = new Request(content: $requestBody);
        $requestValidator = new RequestValidator();

        $errors = $requestValidator->validate(Command::class, $request, RequestType::BODY);

        self::assertIsArray($errors);
        self::assertCount(0, $errors);
    }

    public function testJsonBodyRequestHasError(): void
    {
        $requestBody = '{
            "email": "user@example.com"
        }';
        $request = new Request(content: $requestBody);
        $requestValidator = new RequestValidator();

        $errors = $requestValidator->validate(Command::class, $request, RequestType::BODY);

        self::assertIsArray($errors);
        self::assertCount(1, $errors);
        self::assertEquals('password is required.', $errors[0]);
    }

    public function testJsonBodyRequestHas2Errors(): void
    {
        $requestBody = '{
        }';
        $request = new Request(content: $requestBody);
        $requestValidator = new RequestValidator();

        $errors = $requestValidator->validate(Command::class, $request, RequestType::BODY);

        self::assertIsArray($errors);
        self::assertCount(2, $errors);
        self::assertEquals('email is required.', $errors[0]);
        self::assertEquals('password is required.', $errors[1]);
    }

    public function testGETRequestSuccess(): void
    {
        $GET = [
            'email' => 'user@example.com',
            'password' => 'password'
        ];
        $request = new Request(query: $GET);
        $requestValidator = new RequestValidator();

        $errors = $requestValidator->validate(Command::class, $request, RequestType::GET);

        self::assertIsArray($errors);
        self::assertCount(0, $errors);
    }

    public function testGETRequestHasError(): void
    {
        $GET = [
            'email' => 'user@example.com'
        ];
        $request = new Request(query: $GET);
        $requestValidator = new RequestValidator();

        $errors = $requestValidator->validate(Command::class, $request, RequestType::GET);

        self::assertIsArray($errors);
        self::assertCount(1, $errors);
        self::assertEquals('password is required.', $errors[0]);
    }

    public function testGETRequestHas2Errors(): void
    {
        $GET = [];
        $request = new Request(query: $GET);
        $requestValidator = new RequestValidator();

        $errors = $requestValidator->validate(Command::class, $request, RequestType::GET);

        self::assertIsArray($errors);
        self::assertCount(2, $errors);
        self::assertEquals('email is required.', $errors[0]);
        self::assertEquals('password is required.', $errors[1]);
    }

    public function testPOSTRequestSuccess(): void
    {
        $POST = [
            'email' => 'user@example.com',
            'password' => 'password'
        ];
        $request = new Request(request: $POST);
        $requestValidator = new RequestValidator();

        $errors = $requestValidator->validate(Command::class, $request, RequestType::POST);

        self::assertIsArray($errors);
        self::assertCount(0, $errors);
    }

    public function testPOSTRequestHasError(): void
    {
        $POST = [
            'email' => 'user@example.com'
        ];
        $request = new Request(request: $POST);
        $requestValidator = new RequestValidator();

        $errors = $requestValidator->validate(Command::class, $request, RequestType::POST);

        self::assertIsArray($errors);
        self::assertCount(1, $errors);
        self::assertEquals('password is required.', $errors[0]);
    }

    public function testPOSTRequestHas2Errors(): void
    {
        $POST = [];
        $request = new Request(request: $POST);
        $requestValidator = new RequestValidator();

        $errors = $requestValidator->validate(Command::class, $request, RequestType::POST);

        self::assertIsArray($errors);
        self::assertCount(2, $errors);
        self::assertEquals('email is required.', $errors[0]);
        self::assertEquals('password is required.', $errors[1]);
    }
}