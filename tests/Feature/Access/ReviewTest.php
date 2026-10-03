<?php

namespace Tests\Feature\Access;

use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_未ログインでレビュー編集画面にアクセス(): void
    {
        $review = Review::factory()->create();

        $response = $this->get("/reviews/{$review->id}/edit");

        $response->assertRedirect('/login');
    }

    public function test_ログイン済みでレビュー編集画面にアクセス(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get("/reviews/{$review->id}/edit");

        $response->assertStatus(200);
    }

    public function test_他ユーザーのレビュー編集画面にアクセス(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $userA->id,
        ]);

        $response = $this->actingAs($userB)->get("/reviews/{$review->id}/edit");

        $response->assertStatus(403);
    }
}
