<?php

namespace App\Enums;

enum ReadingPlanStatus: string
{
    case NOT_STARTED = 'not_started';
    case READING = 'reading';
    case COMPLETED = 'completed';
}

// Enumまで完了