<?php

declare(strict_types=1);

namespace App\Model\User\UseCase\Block;

use Symfony\Component\Validator\Constraints as Assert;

final class Command
{
    #[Assert\NotBlank]
    #[Assert\Uuid(strict: false)]
    public string $id;
}
