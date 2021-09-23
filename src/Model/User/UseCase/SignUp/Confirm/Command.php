<?php

declare(strict_types=1);

namespace App\Model\User\UseCase\SignUp\Confirm;

use Symfony\Component\Validator\Constraints as Assert;

final class Command
{
    #[Assert\NotBlank]
    public string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }
}
