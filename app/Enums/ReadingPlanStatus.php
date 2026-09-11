<?php

namespace App\Enums;

enum ReadingPlanStatus: string
{
    case NotStarted = 'not_started';
    case Reading = 'reading';
    case Completed = 'completed';
    case Overdue = 'overdue';

    public function label(): string
    {
        return match ($this) {
            self::NotStarted => '未開始',
            self::Reading => '読書中',
            self::Completed => '読了',
            self::Overdue => '期日超過',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::NotStarted => 'bg-gray-100 text-gray-800',
            self::Reading => 'bg-blue-100 text-blue-800',
            self::Completed => 'bg-green-100 text-green-800',
            self::Overdue => 'bg-red-100 text-red-800',
        };
    }
}
