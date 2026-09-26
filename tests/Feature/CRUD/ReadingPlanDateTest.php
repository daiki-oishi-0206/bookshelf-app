<?php

namespace Tests\Feature\CRUD;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\ReadingPlan;
use Carbon\Carbon;

class ReadingPlanDateTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_読書計画の期日を変更(): void
    {
        $user = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => Carbon::today()->addDays(3),
        ]);

        $newDate = Carbon::today()->addDays(7);

        $response = $this->actingAs($user)
            ->put("/reading-plans/{$readingPlan->id}", [
                'target_date' => $newDate,
            ]);

        $response->assertRedirect('/reading-plans');

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'target_date' => $newDate,
        ]);
    }

    public function test_期日を未入力で読書計画を編集(): void
    {
        $user = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->put("/reading-plans/{$readingPlan->id}", [
                'target_date' => '',
            ]);

        $response->assertStatus(302);

        $response->assertSessionHasErrors([
            'target_date' => '期日は必須です',
        ]);
    }

    public function test_過去の日付で読書計画を編集(): void
    {
        $user = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->put("/reading-plans/{$readingPlan->id}", [
                'target_date' => Carbon::yesterday(),
            ]);

        $response->assertStatus(302);

        $response->assertSessionHasErrors([
            'target_date' => '期日は今日以降の日付を入力してください',
        ]);
    }

    public function test_無効な日付で読書計画を編集(): void
    {
        $user = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->put("/reading-plans/{$readingPlan->id}", [
                'target_date' => 'invalid-date',
            ]);

        $response->assertStatus(302);

        $response->assertSessionHasErrors([
            'target_date' => '期日は有効な日付で入力してください',
        ]);
    }

    public function test_未ログインで読書計画を編集(): void
    {
        $readingPlan = ReadingPlan::factory()->create();

        $response = $this->get("/reading-plans/{$readingPlan->id}/edit");

        $response->assertRedirect('/login');
    }

    public function test_他ユーザーの読書計画を編集(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $otherUser->id,
            'target_date' => Carbon::today()->addDays(3),
        ]);

        $response = $this->actingAs($user)
            ->put("/reading-plans/{$readingPlan->id}", [
                'target_date' => Carbon::today()->addDays(7),
            ]);

        $response->assertStatus(403);

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'user_id' => $otherUser->id,
            'target_date' => Carbon::today()->addDays(3),
        ]);
    }
}
