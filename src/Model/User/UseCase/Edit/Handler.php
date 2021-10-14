<?php

declare(strict_types=1);

namespace App\Model\User\UseCase\Edit;

use App\Model\Flusher;
use App\Model\User\Entity\User\Email;
use App\Model\User\Entity\User\Name;
use App\Model\User\Entity\User\Id;
use App\Model\User\Entity\User\Role;
use App\Model\User\Entity\User\UserRepository;

final class Handler
{
    public function __construct(
        private UserRepository $users,
        private Flusher $flusher
    )
    {}

    public function handle(Command $command): void
    {
//        $user = $this->users->getByEmail(new Email($command->email));
        $user = $this->users->get(new Id($command->id));

        $role = Role::create($command->role);
        if ($role === null) {
            throw new \DomainException("Role {$command->role} does not exists.");
        }

        $user->edit(
            new Email($command->email),
            new Name(
                $command->firstname,
                $command->lastname
            ),
            $role
        );

        $this->flusher->flush();
    }
}
