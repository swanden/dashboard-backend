<?php

declare(strict_types=1);

namespace App\Tests\Builder\User;

use App\Model\User\Entity\User\Email;
use App\Model\User\Entity\User\Name;
use App\Model\User\Entity\User\Role;
use App\Model\User\Entity\User\User;
use App\Model\User\Entity\User\Id;

final class UserBuilder
{
    private Id $id;
    private \DateTimeImmutable $date;

    private Name $name;
    private Email $email;
    private string $hash;
    private string $token;
    private bool $confirmed;
    private bool $blocked;
    private ?Role $role;

    public function __construct()
    {
        $this->id = Id::next();
        $this->date = new \DateTimeImmutable();
        $this->name = new Name('First', 'Last');
        $this->confirmed = false;
        $this->blocked = false;
        $this->role = null;
    }

    public function confirmed(): self
    {
        $clone = clone $this;
        $clone->confirmed = true;
        return $clone;
    }

    public function blocked(): self
    {
        $clone = clone $this;
        $clone->blocked = true;
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

    public function withName(Name $name): self
    {
        $clone = clone $this;
        $clone->name = $name;
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
            $this->name,
            $this->email,
            $this->hash,
            $this->token
        );

        if ($this->confirmed) {
            $user->confirmSignUp();
        }

        if ($this->blocked) {
            $user->block();
        }

        if ($this->role) {
            $user->changeRole($this->role);
        }

        return $user;
    }
}