<?php

declare(strict_types=1);

namespace App\Tests\Builder\User;

use App\Model\User\Entity\User\Email;
use App\Model\User\Entity\User\Role;
use App\Model\User\Entity\User\User;
use App\Model\User\Entity\User\Id;

final class UserBuilder
{
    private Id $id;
    private \DateTimeImmutable $date;

    private Email $email;
    private string $hash;
    private string $token;
    private bool $confirmed;
    private ?Role $role;

    public function __construct()
    {
        $this->id = Id::next();
        $this->date = new \DateTimeImmutable();
        $this->confirmed = false;
        $this->role = null;
    }

    public function confirmed(): self
    {
        $clone = clone $this;
        $clone->confirmed = true;
        return $clone;
    }

    public function viaEmail(Email $email = null, string $hash = null, string $token = null): self
    {
        $clone = clone $this;
        $clone->email = $email ?? new Email('mail@example.com');
        $clone->hash = $hash ?? 'hash';
        $clone->token = $token ?? 'token';
        return $clone;
    }

    public function withRole(Role $role): self
    {
        $clone = clone $this;
        $clone->role = $role;
        return $clone;
    }


    public function build(): User
    {
        $user = new User(
            $this->id,
            $this->date,
            $this->email,
            $this->hash,
            $this->token
        );

        if ($this->confirmed) {
            $user->confirmSignUp();
        }

        if ($this->role) {
            $user->changeRole($this->role);
        }

        return $user;
    }
}