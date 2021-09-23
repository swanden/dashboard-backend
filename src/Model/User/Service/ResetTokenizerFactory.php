<?php

declare(strict_types=1);

namespace App\Model\User\Service;

final class ResetTokenizerFactory
{
    public function create(string $interval): ResetTokenizer
    {
        return new ResetTokenizer(new \DateInterval($interval));
    }
}