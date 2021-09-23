<?php

namespace App\DataFixtures;

use App\Model\User\Entity\User\Email;
use App\Model\User\Entity\User\Role;
use App\Model\User\Entity\User\User;
use App\Model\User\Entity\User\Id;
use App\Model\User\Service\PasswordHasher;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Trikoder\Bundle\OAuth2Bundle\Model\Client;
use Trikoder\Bundle\OAuth2Bundle\Model\Grant;
use Trikoder\Bundle\OAuth2Bundle\OAuth2Grants;

final class UserFixture extends Fixture
{
    public function __construct(
        private PasswordHasher $hasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        $hash = $this->hasher->hash('password');

        $user = new User(
            Id::next(),
            new \DateTimeImmutable(),
            new Email('admin@example.com'),
            $hash,
            'token'
        );

        $user->confirmSignUp();

        $user->changeRole(Role::ADMIN);

        $manager->persist($user);

        $client = new Client('oauth_client', 'secret');
        $client->setGrants(new Grant(OAuth2Grants::PASSWORD));

        $manager->persist($client);

        $manager->flush();
    }
}