<?php

declare(strict_types=1);

namespace App\Model\User\Entity\User;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="user_users", uniqueConstraints={
 *     @ORM\UniqueConstraint(columns={"email"}),
 *     @ORM\UniqueConstraint(columns={"reset_token_token"})
 * })
 */
final class User
{
    private const STATUS_WAIT = 'wait';
    public const STATUS_ACTIVE = 'active';

    /**
     * @ORM\Column(type="string", length=16)
     */
    private string $status;
    /**
     * @ORM\Embedded(class="ResetToken", columnPrefix="reset_token_")
     */
    private ?ResetToken $resetToken;
    /**
     * @ORM\Column(type="user_user_role", length=16)
     */
    private Role $role;

    public function __construct(
        /**
         * @ORM\Column(type="user_user_id")
         * @ORM\Id
         */
        private Id $id,
        /**
         * @ORM\Column(type="datetime_immutable")
         */
        private \DateTimeImmutable $date,
        /**
         * @ORM\Embedded(class="Name")
         */
        private Name $name,
        /**
         * @ORM\Column(type="user_user_email", nullable=true)
         */
        private Email $email,
        /**
         * @ORM\Column(type="string", name="password_hash", nullable=true)
         */
        private string $passwordHash,
        /**
         * @ORM\Column(type="string", name="confirm_token", nullable=true)
         */
        private ?string $confirmToken
    )
    {
        $this->role = Role::USER;
        $this->status = self::STATUS_WAIT;
        $this->resetToken = null;
    }

    public function confirmSignUp(): void
    {
        if (!$this->isWait()) {
            throw new \DomainException('User has already been confirmed.');
        }

        $this->status = self::STATUS_ACTIVE;
        $this->confirmToken = null;
    }

    public function requestPasswordReset(ResetToken $token, \DateTimeImmutable $date): void
    {
        if (!$this->isActive()) {
            throw new \DomainException('User is not active.');
        }
        if (!$this->email) {
            throw new \DomainException('User email is empty.');
        }
        if ($this->resetToken && !$this->resetToken->isExpiredTo($date)) {
            throw new \DomainException('Password reset has already been requested.');
        }

        $this->resetToken = $token;
    }

    public function passwordReset(\DateTimeImmutable $date, string $hash): void
    {
        if (!$this->resetToken) {
            throw new \DomainException('Reset is not requested.');
        }
        if ($this->resetToken->isExpiredTo($date)) {
            throw new \DomainException('Token expired.');
        }
        $this->passwordHash = $hash;
        $this->resetToken = null;
    }

    public function changeName(Name $name): void
    {
        $this->name = $name;
    }

    public function changeRole(Role $role): void
    {
        if ($this->role->isEqual($role)) {
            throw new \DomainException('Role has already been set.');
        }
        $this->role = $role;
    }

    public function isWait(): bool
    {
        return $this->status === self::STATUS_WAIT;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function getId(): Id
    {
        return $this->id;
    }

    public function getName(): Name
    {
        return $this->name;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function getConfirmToken(): ?string
    {
        return $this->confirmToken;
    }

    public function getResetToken(): ?ResetToken
    {
        return $this->resetToken;
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @ORM\PostLoad()
     */
    public function checkEmbeds(): void
    {
        if ($this->resetToken->isEmpty()) {
            $this->resetToken = null;
        }
    }
}
