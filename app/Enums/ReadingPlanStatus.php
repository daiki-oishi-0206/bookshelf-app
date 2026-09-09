<?php

namespace App\Enums;

enum ReadingPlanStatus: string
{
    case NOT_STARTED = 'not_started';
    case READING = 'reading';
    case COMPLETED = 'completed';
    case OVERDUE = 'overdue';

    public function label(): string
    {
        return match ($this) {
            self::NOT_STARTED => '未開始',
            self::READING => '読書中',
            self::COMPLETED => '読了',
            self::OVERDUE => '期日超過',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::NOT_STARTED => 'bg-gray-100 text-gray-800',
            self::READING => 'bg-blue-100 text-blue-800',
            self::COMPLETED => 'bg-green-100 text-green-800',
            self::OVERDUE => 'bg-red-100 text-red-800',
        };
    }
}
