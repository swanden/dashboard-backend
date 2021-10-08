<?php

declare(strict_types=1);

namespace App\Infrastructure\ReadModel\User;

use App\Model\User\Entity\User\User;
use App\ReadModel\NotFoundException;
use App\ReadModel\User\AuthView;
use App\ReadModel\User\UserFetcher;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

final class DoctrineUserFetcher implements UserFetcher
{
    private EntityRepository $repository;

    public function __construct(
        private Connection $connection,
        EntityManagerInterface $em
    ) {
        $this->repository = $em->getRepository(User::class);
    }

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

    /**
     * @throws NotFoundException
     */
    public function get(string $id): User
    {
        if (!$user = $this->repository->find($id)) {
            throw new NotFoundException('User is not found');
        }
        return $user;
    }
}
