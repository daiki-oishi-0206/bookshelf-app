<?php

namespace Tests\Feature;

use App\Models\Favorite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Review;

class ReviewTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_未ログインでレビュー編集画面にアクセスするとログイン画面へリダイレクトされる(): void
    {
        $review = Review::factory()->create();

        $response = $this->get("/reviews/{$review->id}/edit");

        $response->assertRedirect('/login');
    }

    public function test_ログイン済みでレビュー編集画面にアクセスできる(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get("/reviews/{$review->id}/edit");

        $response->assertStatus(200);
    }

    public function test_他ユーザーのレビュー編集画面にアクセスすると403が返る(): void
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
