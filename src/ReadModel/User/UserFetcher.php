<?php

declare(strict_types=1);

namespace App\ReadModel\User;

interface UserFetcher
{
    public function existsByResetToken(string $token): bool;

    public function findForAuth(string $email): ?AuthView;
}
