<?php

namespace App\Enums;

enum MassageRole: string
{
    case User = 'user';
    case Assistant = 'assistant';
    case System = 'system';

    public function label(): string
    {
        return match ($this) {
            self::User => 'المستخدم',
            self::Assistant => 'المساعد',
            self::System => 'النظام',
        };
    }
}
