<?php

namespace App\Model\User\Entity\User;

enum Role: string
{
    case USER = 'ROLE_USER';
    case ADMIN = 'ROLE_ADMIN';

    public function isAdmin(): bool
    {
        return $this->name === self::ADMIN->name;
    }

    public function isUser(): bool
    {
        return $this->name === self::USER->name;
    }

    public function isEqual(self $role): bool
    {
        return $this->name === $role->name;
    }

    public function getName(): string
    {
        return $this->name;
    }
}