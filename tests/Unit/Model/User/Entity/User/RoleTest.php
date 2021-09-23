<?php

declare(strict_types=1);

namespace App\Tests\Unit\Model\User\Entity\User;

use App\Model\User\Entity\User\Role;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

final class RoleTest extends TestCase
{
    public function testSuccess(): void
    {
        $user = (new UserBuilder())->viaEmail()->build();

        $user->changeRole(Role::ADMIN);

        self::assertFalse($user->getRole()->isUser());
        self::assertTrue($user->getRole()->isAdmin());
    }

    public function testAlready(): void
    {
        $user = (new UserBuilder())->viaEmail()->build();

        $this->expectExceptionMessage('Role has already been set.');

        $user->changeRole(Role::USER);
    }
}