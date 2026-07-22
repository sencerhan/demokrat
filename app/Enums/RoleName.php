<?php

namespace App\Enums;

enum RoleName: string
{
    case SuperAdmin = 'superadmin';
    case Admin = 'admin';
    case Moderator = 'moderator';
    case Member = 'member';

    /**
     * Roles that grant moderation powers over talepler.
     *
     * @return array<int, string>
     */
    public static function moderatorAndAbove(): array
    {
        return [
            self::Moderator->value,
            self::Admin->value,
            self::SuperAdmin->value,
        ];
    }
}
