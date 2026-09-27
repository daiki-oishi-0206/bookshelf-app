<?php

namespace App\Enums;

enum ReadingPlanStatus: string
{
    case Reading = 'reading';
    case Completed = 'completed';
    case Overdue = 'overdue';

    public function label(): string
    {
        return match ($this) {
            self::Reading => '進行中',
            self::Completed => '読了',
            self::Overdue => '期日超過',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Reading => 'bg-blue-100 text-blue-800',
            self::Completed => 'bg-green-100 text-green-800',
            self::Overdue => 'bg-red-100 text-red-800',
        };
    }
}