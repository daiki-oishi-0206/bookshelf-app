<?php

namespace Tests\Feature\Access;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\ReadingPlan;
use App\Notifications\ReadingPlanReminderNotification;

class NotificationTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_未ログインで通知一覧画面にアクセス(): void
    {
        $response = $this->get('/notifications');

        $response->assertRedirect('/login');
    }

    public function test_ログイン済みで通知一覧画面にアクセス(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/notifications');

        $response->assertStatus(200);
    }

    public function test_他ユーザーの通知がある状態で通知一覧画面にアクセス(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $otherUser->notify(
            new ReadingPlanReminderNotification(
                $readingPlan,
                'three_days_before'
            )
        );

        $response = $this->actingAs($user)->get('/notifications');
        
        $response->assertStatus(200);

        $response->assertDontSee($readingPlan->book->title);
    }
}
