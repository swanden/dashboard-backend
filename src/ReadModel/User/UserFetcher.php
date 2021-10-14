<?php

declare(strict_types=1);

namespace App\ReadModel\User;

use App\Model\User\Entity\User\User;

interface UserFetcher
{
    public function existsByResetToken(string $token): bool;

    public function findForAuth(string $email): ?AuthView;

    public function get(string $id): User;

    /**
     * @return array<User>
     */
    public function all(): array;
}
