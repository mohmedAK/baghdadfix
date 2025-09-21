<?php
namespace App\Enums;

enum UserRole: string
{
    case Admin     = 'admin';
    case Technical = 'technical';
    case Customer  = 'customer';

    public static function labels(): array
    {
        return [
            self::Admin->value     => 'Admin',
            self::Technical->value => 'Technician',
            self::Customer->value  => 'Customer',
        ];
    }
}
