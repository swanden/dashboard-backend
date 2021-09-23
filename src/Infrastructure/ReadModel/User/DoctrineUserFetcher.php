<?php

declare(strict_types=1);

namespace App\Infrastructure\ReadModel\User;

use App\ReadModel\User\AuthView;
use App\ReadModel\User\UserFetcher;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;

final class DoctrineUserFetcher implements UserFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    public function existsByResetToken(string $token): bool
    {
        return $this->connection->createQueryBuilder()
                ->select('COUNT (*)')
                ->from('user_users')
                ->where('reset_token_token = :token')
                ->setParameter(':token', $token)
                ->execute()->fetchColumn(0) > 0;
    }

    public function findForAuth(string $email): ?AuthView
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'id',
                'email',
                'password_hash',
                'role',
                'status'
            )
            ->from('user_users')
            ->where('email = :email')
            ->setParameter(':email', $email)
            ->execute();

        $stmt->setFetchMode(FetchMode::CUSTOM_OBJECT, AuthView::class);
        $result = $stmt->fetch();

        return $result ?: null;
    }
}
