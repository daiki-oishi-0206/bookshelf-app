<?php

namespace App\Console\Commands;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Notifications\ReadingPlanReminderNotification;
use Illuminate\Console\Command;


class SendReadingPlanReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reading-plans:remind';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '読書計画の失効処理とリマインダー通知を実行する';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        ReadingPlan::query()
            ->whereIn('status', [
                ReadingPlanStatus::NOT_STARTED,
                ReadingPlanStatus::READING,
            ])
            ->whereDate('target_date', '<', today())
            ->update([
                'status' => ReadingPlanStatus::OVERDUE,
            ]);


        $readingPlans = ReadingPlan::query()
            ->whereIn('status', [
                ReadingPlanStatus::NOT_STARTED,
                ReadingPlanStatus::READING,
            ])
            ->whereDate('target_date', today()->addDays(3))
            ->with(['user', 'book'])
            ->get();

        foreach ($readingPlans as $readingPlan){
            $readingPlan->user->notify(
                new ReadingPlanReminderNotification(
                    $readingPlan,
                    'three_days_before'
                )
            );
        }

        $readingPlans = ReadingPlan::query()
            ->whereIn('status', [
                ReadingPlanStatus::NOT_STARTED,
                ReadingPlanStatus::READING,
            ])
            ->whereDate('target_date', today())
            ->with(['user', 'book'])
            ->get();

        foreach ($readingPlans as $readingPlan){
            $readingPlan->user->notify(
                new ReadingPlanReminderNotification(
                    $readingPlan,
                    'on_due_date'
                )
            );
        }

        $readingPlans = ReadingPlan::query()
            ->where('status', ReadingPlanStatus::OVERDUE)
            ->whereDate('target_date', today()->subDays(3))
            ->with(['user', 'book'])
            ->get();

        foreach ($readingPlans as $readingPlan){
            $readingPlan->user->notify(
                new ReadingPlanReminderNotification(
                    $readingPlan,
                    'three_days_after'
                )
            );
        }

        return self::SUCCESS;
    }
}


