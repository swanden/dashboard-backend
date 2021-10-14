<?php

namespace App\Tests\Unit\Model\User\Entity\User;

use App\Model\User\Entity\User\Email;
use App\Model\User\Entity\User\Name;
use App\Model\User\Entity\User\Role;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

final class EditTest extends TestCase
{
    public function testSuccess()
    {
        $user = (new UserBuilder())->viaEmail()->build();

        $user->edit(
            $email = new Email('new-test-user@example.com'),
            $name = new Name('New','User'),
            $role = Role::ADMIN
        );

        self::assertEquals($email, $user->getEmail());
        self::assertEquals($name, $user->getName());
        self::assertEquals($role, $user->getRole());
    }
}