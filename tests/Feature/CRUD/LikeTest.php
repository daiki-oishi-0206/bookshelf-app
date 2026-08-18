<?php

namespace Tests\Feature\CRUD;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Review;

class LikeTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_レビューにいいねを登録できる(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create();
        $beforeCount = $review->likes()->count();

        $response = $this->actingAs($user)
            ->from("/books/{$review->book_id}")
            ->post("/reviews/{$review->id}/like");

        $afterCount = $review->likes()->count();

        $response->assertRedirect("/books/{$review->book_id}");

        $this->assertSame($beforeCount + 1, $afterCount);

        $this->assertDatabaseHas('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    public function test_存在しないレビューにいいねを登録できない(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post("/reviews/99/like");

        $response->assertStatus(404);
    }

    public function test_未ログインでいいねを登録できない(): void
    {
        $review = Review::factory()->create();

        $response = $this->post("/reviews/{$review->id}/like");

        $response->assertRedirect('/login');

        $this->assertDatabaseMissing('review_likes', [
            'review_id' => $review->id,
        ]);
    }

    public function test_レビューのいいねを解除できる(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create();

        $user->likedReviews()->attach($review->id);

        $beforeCount = $review->likes()->count();

        $response = $this->actingAs($user)
            ->from("/books/{$review->book_id}")
            ->post("/reviews/{$review->id}/like");

        $afterCount = $review->likes()->count();

        $response->assertRedirect("/books/{$review->book_id}");

        $this->assertSame($beforeCount - 1, $afterCount);

        $this->assertDatabaseMissing('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    public function test_未ログインでいいねを解除できない(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create();

        $user->likedReviews()->attach($review->id);

        $response = $this->post("/reviews/{$review->id}/like");

        $response->assertRedirect('/login');

        $this->assertDatabaseHas('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }
}
