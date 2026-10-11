<?php

namespace Tests\Feature\CRUD;

use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LikeTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Review $review;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->review = Review::factory()->create();
    }

    public function test_レビューにいいねを登録できる(): void
    {
        $beforeCount = $this->review->likes()->count();

        $response = $this->actingAs($this->user)
            ->from("/books/{$this->review->book_id}")
            ->post("/reviews/{$this->review->id}/like");

        $afterCount = $this->review->likes()->count();

        $response->assertRedirect("/books/{$this->review->book_id}");

        $this->assertSame($beforeCount + 1, $afterCount);

        $this->assertDatabaseHas('review_likes', [
            'user_id' => $this->user->id,
            'review_id' => $this->review->id,
        ]);
    }

    public function test_存在しないレビューにいいねを登録できない(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/reviews/99/like');

        $response->assertStatus(404);
    }

    public function test_未ログインでいいねを登録できない(): void
    {
        $response = $this->post("/reviews/{$this->review->id}/like");

        $response->assertRedirect('/login');

        $this->assertDatabaseMissing('review_likes', [
            'review_id' => $this->review->id,
        ]);
    }

    public function test_レビューのいいねを解除できる(): void
    {
        $this->user->likedReviews()->attach($this->review->id);

        $beforeCount = $this->review->likes()->count();

        $response = $this->actingAs($this->user)
            ->from("/books/{$this->review->book_id}")
            ->post("/reviews/{$this->review->id}/like");

        $afterCount = $this->review->likes()->count();

        $response->assertRedirect("/books/{$this->review->book_id}");

        $this->assertSame($beforeCount - 1, $afterCount);

        $this->assertDatabaseMissing('review_likes', [
            'user_id' => $this->user->id,
            'review_id' => $this->review->id,
        ]);
    }

    public function test_未ログインでいいねを解除できない(): void
    {
        $this->user->likedReviews()->attach($this->review->id);

        $response = $this->post("/reviews/{$this->review->id}/like");

        $response->assertRedirect('/login');

        $this->assertDatabaseHas('review_likes', [
            'user_id' => $this->user->id,
            'review_id' => $this->review->id,
        ]);
    }
}
