<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Model\User\Entity\User\Email;
use App\Model\User\Entity\User\Role;
use App\Model\User\Service\PasswordHasher;
use App\Tests\Builder\User\UserBuilder;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class AuthFixture extends Fixture
{
    public const REFERENCE_ADMIN = 'test_auth_admin';
    public const REFERENCE_USER = 'test_auth_user';

    public function __construct(
        private PasswordHasher $hasher
    ) {}

    public static function userCredentials(): array
    {
        return [
            'PHP_AUTH_USER' => 'auth-user@example.com',
            'PHP_AUTH_PW' => 'password',
        ];
    }

    public static function adminCredentials(): array
    {
        return [
            'PHP_AUTH_USER' => 'auth-admin@example.com',
            'PHP_AUTH_PW' => 'password',
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $hash = $this->hasher->hash('password');

        $user = (new UserBuilder())
            ->viaEmail(new Email('auth-user@example.com'), $hash)
            ->confirmed()
            ->build();

        $manager->persist($user);
        $this->setReference(self::REFERENCE_USER, $user);

        $admin = (new UserBuilder())
            ->viaEmail(new Email('auth-admin@example.com'), $hash)
            ->confirmed()
            ->withRole(Role::ADMIN)
            ->build();

        $manager->persist($admin);
        $this->setReference(self::REFERENCE_ADMIN, $admin);

        $manager->flush();
    }
}
