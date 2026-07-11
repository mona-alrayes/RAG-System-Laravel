<?php

namespace App\Enums;

enum DocumentStatus : string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'بانتظار المعالجة',
            self::Processing => 'قيد المعالجة',
            self::Completed => 'اكتملت المعالجة',
            self::Failed => 'فشلت المعالجة',
        };  
    }
}

