<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Type\User;

use App\Model\User\Entity\User\Role;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class RoleType extends StringType
{
    public const NAME = 'user_user_role';

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): mixed
    {
        return $value instanceof Role ? $value->value: $value;
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?Role
    {
        return !empty($value) ? Role::from($value) : null;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}