<?php

declare(strict_types=1);

namespace App\Model\User\UseCase\SignUp\Confirm;

use App\Model\Flusher;
use App\Model\User\Entity\User\UserRepository;

final class Handler
{
    public function __construct(
        private UserRepository $users,
        private Flusher $flusher
    ) {}

    public function handle(Command $command): void
    {
        if (!$user = $this->users->findByConfirmToken($command->token)) {
            throw new \DomainException('Wrong or confirmed token.');
        }

        $user->confirmSignUp();

        $this->flusher->flush();
    }
}
