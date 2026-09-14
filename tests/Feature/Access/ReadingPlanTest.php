<?php

namespace Tests\Feature\Access;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\ReadingPlan;

class ReadingPlanTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_未ログインで読書計画一覧画面にアクセス(): void
    {
        $response = $this->get('/reading-plans');

        $response->assertRedirect('/login');
    }

    public function test_ログイン済みで読書計画一覧画面にアクセス(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/reading-plans');

        $response->assertStatus(200);
    }

    public function test_未ログインで読書計画作成画面にアクセス(): void
    {
        $response = $this->get('/reading-plans/create');

        $response->assertRedirect('/login');
    }

    public function test_ログイン済みで読書計画作成画面にアクセス(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/reading-plans/create');

        $response->assertStatus(200);
    }

    public function test_未ログインで読書計画編集画面にアクセス(): void
    {
        $readingPlan = ReadingPlan::factory()->create();

        $response = $this->get("/reading-plans/{$readingPlan->id}/edit");

        $response->assertRedirect('/login');
    }

    public function test_ログイン済みで自分の読書計画編集画面にアクセス(): void
    {
        $user = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get("/reading-plans/{$readingPlan->id}/edit");

        $response->assertStatus(200);
    }

}
