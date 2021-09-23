<?php

declare(strict_types=1);


namespace App\Model\User\Service;

use App\Model\User\Entity\User\ResetToken;
use Ramsey\Uuid\Uuid;

final class ResetTokenizer
{
    public function __construct(
        private \DateInterval $interval
    ) {}

    public function generate(): ResetToken
    {
        return new ResetToken(
            Uuid::uuid4()->toString(),
            (new \DateTimeImmutable())->add($this->interval)
        );
    }
}
