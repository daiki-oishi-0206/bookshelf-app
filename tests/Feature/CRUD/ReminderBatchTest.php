<?php

namespace Tests\Feature\CRUD;

use App\Models\ReadingPlan;
use App\Models\User;
use App\Notifications\ReadingPlanReminderNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReminderBatchTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_期日の3日前にリマインダーが通知される(): void
    {
        $user = User::factory()->create();

        $readingPlanA = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => 'reading',
            'target_date' => Carbon::today()->addDays(3),
        ]);

        $readingPlanB = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => 'completed',
            'target_date' => Carbon::today()->addDays(3),
        ]);

        $this->artisan('reading-plans:remind');

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'notifiable_type' => User::class,
            'type' => ReadingPlanReminderNotification::class,
            'data' => json_encode([
                'title' => '読書計画のお知らせ',
                'body' => "「{$readingPlanA->book->title}」の期日まであと3日です",
                'timing' => 'three_days_before',
            ]),
        ]);

        $this->assertDatabaseMissing('notifications', [
            'notifiable_id' => $user->id,
            'notifiable_type' => User::class,
            'type' => ReadingPlanReminderNotification::class,
            'data' => json_encode([
                'title' => '読書計画のお知らせ',
                'body' => "「{$readingPlanB->book->title}」の期日まであと3日です",
                'timing' => 'three_days_before',
            ]),
        ]);
    }

    public function test_期日にリマインダーが通知される(): void
    {
        $user = User::factory()->create();

        $readingPlanA = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => 'reading',
            'target_date' => Carbon::today(),
        ]);

        $readingPlanB = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => 'completed',
            'target_date' => Carbon::today(),
        ]);

        $this->artisan('reading-plans:remind');

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'notifiable_type' => User::class,
            'type' => ReadingPlanReminderNotification::class,
            'data' => json_encode([
                'title' => '読書計画のお知らせ',
                'body' => "「{$readingPlanA->book->title}」の期日です",
                'timing' => 'on_due_date',
            ]),
        ]);

        $this->assertDatabaseMissing('notifications', [
            'notifiable_id' => $user->id,
            'notifiable_type' => User::class,
            'type' => ReadingPlanReminderNotification::class,
            'data' => json_encode([
                'title' => '読書計画のお知らせ',
                'body' => "「{$readingPlanB->book->title}」の期日です",
                'timing' => 'on_due_date',
            ]),
        ]);
    }

    public function test_期限の3日後にリマインダーが通知される(): void
    {
        $user = User::factory()->create();

        $readingPlanA = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => 'overdue',
            'target_date' => Carbon::today()->subDays(3),
        ]);

        $readingPlanB = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => 'completed',
            'target_date' => Carbon::today()->subDays(3),
        ]);

        $this->artisan('reading-plans:remind');

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'notifiable_type' => User::class,
            'type' => ReadingPlanReminderNotification::class,
            'data' => json_encode([
                'title' => '読書計画のお知らせ',
                'body' => "「{$readingPlanA->book->title}」の期日を3日過ぎています",
                'timing' => 'three_days_after',
            ]),
        ]);

        $this->assertDatabaseMissing('notifications', [
            'notifiable_id' => $user->id,
            'notifiable_type' => User::class,
            'type' => ReadingPlanReminderNotification::class,
            'data' => json_encode([
                'title' => '読書計画のお知らせ',
                'body' => "「{$readingPlanB->book->title}」の期日を3日過ぎています",
                'timing' => 'three_days_after',
            ]),
        ]);
    }

    public function test_リマインダーの対象となる読書計画がない(): void
    {
        $response = $this->artisan('reading-plans:remind');

        $response->assertExitCode(0);

        $this->assertDatabaseCount('notifications', 0);
    }
}
