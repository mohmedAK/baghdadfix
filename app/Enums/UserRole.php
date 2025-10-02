<?php
namespace App\Enums;

enum UserRole: string
{
    case Admin     = 'admin';
    case Technical = 'technical';
    case Customer  = 'customer';
    case Editor    = 'editor';

    public static function labels(): array
    {
        return [
            self::Admin->value     => 'Admin',
            self::Editor->value   => 'Editor',
            self::Technical->value => 'Technician',
            self::Customer->value  => 'Customer',
        ];
    }
}
