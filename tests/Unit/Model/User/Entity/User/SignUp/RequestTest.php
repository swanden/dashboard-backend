<?php

declare(strict_types=1);

namespace App\Tests\Unit\Model\User\Entity\User\SignUp;

use App\Model\User\Entity\User\Email;
use App\Model\User\Entity\User\Id;
use App\Model\User\Entity\User\Name;
use App\Model\User\Entity\User\User;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
    public function testSuccess(): void
    {
        $user = new User(
            $id = Id::next(),
            $date = new \DateTimeImmutable(),
            $name = new Name('Firstname', 'Lastname'),
            $email = new Email('test@app.test'),
            $hash = 'hash',
            $token = 'token'
        );

        self::assertTrue($user->isWait());
        self::assertFalse($user->isActive());

        self::assertEquals($id, $user->getId());
        self::assertEquals($date, $user->getDate());
        self::assertEquals($email, $user->getEmail());
        self::assertEquals($name, $user->getName());
        self::assertEquals($hash, $user->getPasswordHash());

        self::assertTrue($user->getRole()->isUser());
    }
}
