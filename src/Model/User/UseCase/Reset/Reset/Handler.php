<?php

declare(strict_types=1);

namespace App\Model\User\UseCase\Reset\Reset;

use App\Model\Flusher;
use App\Model\User\Entity\User\UserRepository;
use App\Model\User\Service\PasswordHasher;

final class Handler
{
    public function __construct(
        private UserRepository $users,
        private PasswordHasher $hasher,
        private Flusher $flusher
    ) {}

    public function handle(Command $command): void
    {
        if (!$user = $this->users->findByResetToken($command->token)) {
            throw new \DomainException('Wrong or expired token.');
        }

        $user->passwordReset(
            new \DateTimeImmutable(),
            $this->hasher->hash($command->password)
        );

        $this->flusher->flush();
    }
}