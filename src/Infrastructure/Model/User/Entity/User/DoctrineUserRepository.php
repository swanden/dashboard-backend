<?php

declare(strict_types=1);

namespace App\Infrastructure\Model\User\Entity\User;

use App\Model\User\Entity\User\UserRepository;
use App\Model\EntityNotFoundException;
use App\Model\User\Entity\User\Email;
use App\Model\User\Entity\User\Id;
use App\Model\User\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

final class DoctrineUserRepository implements UserRepository
{
    private EntityRepository $repo;

    public function __construct(
        private EntityManagerInterface $em
    )
    {
        $this->repo = $em->getRepository(User::class);
    }

    public function findByConfirmToken(string $token): ?User
    {
        return $this->repo->findOneBy(['confirmToken' => $token]);
    }

    public function findByResetToken(string $token): ?User
    {
        return $this->repo->findOneBy(['resetToken.token' => $token]);
    }

    public function get(Id $id): User
    {
        /** @var User $user */
        if (!$user = $this->repo->find($id->getValue())) {
            throw new EntityNotFoundException('User is not found.');
        }
        return $user;
    }

    public function getByEmail(Email $email): User
    {
        /** @var User $user */
        if (!$user = $this->repo->findOneBy(['email' => $email->getValue()])) {
            throw new EntityNotFoundException('User is not found.');
        }
        return $user;
    }

    public function hasByEmail(Email $email): bool
    {
        return $this->repo->createQueryBuilder('t')
                ->select('COUNT(t.id)')
                ->andWhere('t.email = :email')
                ->setParameter(':email', $email->getValue())
                ->getQuery()->getSingleScalarResult() > 0;
    }

    public function add(User $user): void
    {
        $this->em->persist($user);
    }
}
