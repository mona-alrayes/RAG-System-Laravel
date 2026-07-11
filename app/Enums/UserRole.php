<?php

namespace App\Enums;


/**
 * User roles enumeration.
 * This enum defines the different roles that a user can have in the system.
 * label() method provides a human-readable label for each role.
 * it is used to represent user roles in a type-safe manner throughout the application. like filament and other places.
 */

enum UserRole : string
{
    case Admin = 'admin';
    case User = 'user';


    public function label(): string
    {
        return match($this) {
            self::Admin => 'مدير', 
            self::User => 'مستخدم',
        };
    }
}
