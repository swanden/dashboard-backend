<?php

declare(strict_types=1);

namespace App\Model\User\UseCase\Edit;

use App\Model\User\Entity\User\User;
use Symfony\Component\Validator\Constraints as Assert;

final class Command
{
    #[Assert\NotBlank]
//    #[Assert\Uuid(versions: [4], strict: false)]
    #[Assert\Uuid(strict: false)]
    public string $id;

    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email;

    #[Assert\NotBlank]
    public string $firstname;

    #[Assert\NotBlank]
    public string $lastname;

    #[Assert\NotBlank]
    public string $role;

//    public function __construct(string $id)
//    {
//        $this->id = $id;
//    }
//
//    public static function fromUser(User $user): self
//    {
//        $command = new self($user->getId()->getValue());
//        $command->email = $user->getEmail()?->getValue();
//        $command->firstname = $user->getName()->getFirst();
//        $command->lastname = $user->getName()->getLast();
//        return $command;
//    }
}
