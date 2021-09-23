<?php

declare(strict_types=1);

namespace App\Tests\Functional\OAuth;

use App\Model\User\Entity\User\Email;
use App\Tests\Builder\User\UserBuilder;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Trikoder\Bundle\OAuth2Bundle\Model\Client;
use Trikoder\Bundle\OAuth2Bundle\Model\Grant;
use Trikoder\Bundle\OAuth2Bundle\OAuth2Grants;
use App\Model\User\Service\PasswordHasher;

final class OAuthFixture extends Fixture
{
    public function __construct(
        private PasswordHasher $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        $user = (new UserBuilder())
            ->viaEmail(
                new Email('oauth-password-user@example.com'),
                $this->passwordHasher->hash('password')
            )
            ->confirmed()
            ->build();

        $manager->persist($user);

        $client = new Client('oauth', 'secret');
        $client->setGrants(new Grant(OAuth2Grants::PASSWORD));

        $manager->persist($client);

        $manager->flush();
    }
}
