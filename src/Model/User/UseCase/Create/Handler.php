<?php

declare(strict_types=1);

namespace App\Model\User\UseCase\Create;

use App\Model\Flusher;
use App\Model\User\Entity\User\Email;
use App\Model\User\Entity\User\Name;
use App\Model\User\Entity\User\Role;
use App\Model\User\Entity\User\User;
use App\Model\User\Entity\User\Id;
use App\Model\User\Entity\User\UserRepository;
use App\Model\User\Service\PasswordGenerator;
use App\Model\User\Service\PasswordHasher;

class Handler
{
    public function __construct(
        private UserRepository $users,
        private PasswordHasher $hasher,
        private PasswordGenerator $generator,
        private Flusher $flusher
    )
    {}

    public function handle(Command $command): string
    {
        $email = new Email($command->email);

        if ($this->users->hasByEmail($email)) {
            throw new \DomainException('User with this email already exists.');
        }

        $role = Role::create($command->role);
        if ($role === null) {
            throw new \DomainException("Role {$command->role} does not exists.");
        }

        $user = User::create(
            $id = Id::next(),
            new \DateTimeImmutable(),
            new Name(
                $command->firstname,
                $command->lastname
            ),
            $email,
            $role,
            $this->hasher->hash($this->generator->generate())
        );

        $this->users->add($user);

        $this->flusher->flush();

        return $id->getValue();
    }
}
