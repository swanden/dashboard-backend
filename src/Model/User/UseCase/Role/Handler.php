<?php

declare(strict_types=1);

namespace App\Model\User\UseCase\Role;

use App\Model\Flusher;
use App\Model\User\Entity\User\Id;
use App\Model\User\Entity\User\Role;
use App\Model\User\Entity\User\UserRepository;

final class Handler
{
    public function __construct(
        private UserRepository $users,
        private Flusher $flusher
    ) {}

    public function handle(Command $command): void
    {
        $user = $this->users->get(new Id($command->id));

        $user->changeRole(Role::from($command->role));

        $this->flusher->flush();
    }
}