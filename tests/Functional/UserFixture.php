<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Model\User\Entity\User\Email;
use App\Model\User\Entity\User\Role;
use App\Model\User\Service\PasswordHasher;
use App\Tests\Builder\User\UserBuilder;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class UserFixture extends Fixture
{
    public function __construct(
        private PasswordHasher $hasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        $hash = $this->hasher->hash('password');

        $user = (new UserBuilder())
            ->viaEmail(new Email('test-user@example.com'), $hash)
            ->confirmed()
            ->build();

        $manager->persist($user);

        $admin = (new UserBuilder())
            ->viaEmail(new Email('test-admin@example.com'), $hash)
            ->confirmed()
            ->withRole(Role::ADMIN)
            ->build();

        $manager->persist($admin);

        $manager->flush();
    }
}
