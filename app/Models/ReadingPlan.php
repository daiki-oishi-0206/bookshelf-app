<?php

namespace App\Models;

use App\Enums\ReadingPlanStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReadingPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'book_id',
        'status',
        'target_date',
    ];

    protected $casts = [
        'status' => ReadingPlanStatus::class,
        'target_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function scopeNotStarted(Builder $query)
    {
        return $query->where('status', ReadingPlanStatus::NotStarted);
    }

    public function scopeReading(Builder $query)
    {
        return $query->where('status', ReadingPlanStatus::Reading);
    }

    public function scopeCompleted(Builder $query)
    {
        return $query->where('status', ReadingPlanStatus::Completed);
    }
}
