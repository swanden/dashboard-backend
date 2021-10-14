<?php

namespace App\Tests\Unit\Model\User\Entity\User;

use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

final class BlockTest extends TestCase
{
    public function testSuccess(): void
    {
        $user = (new UserBuilder())->viaEmail()->build();

        $user->block();

        self::assertTrue($user->isBlocked());
        self::assertFalse($user->isActive());
    }

    public function testUserAlreadyBlocked()
    {
        $user = (new UserBuilder())->viaEmail()->build();
        $user->block();

        $this->expectExceptionMessage('User is already blocked.');

        $user->block();
    }
}