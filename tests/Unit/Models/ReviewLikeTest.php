<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Review;
use App\Models\ReviewLike;

class ReviewLikeTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic unit test example.
     */
    public function test_ReviewLikeモデルが正常に保存できる(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
        ]);
        $reviewLike = ReviewLike::factory()->create([
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);

        $this->assertDatabaseHas('review_likes', [
            'id' => $reviewLike->id,
        ]);
    }

    public function test_ReviewLikeモデルのリレーションが正しく取得できる(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
        ]);
        $reviewLike = ReviewLike::factory()->create([
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);

        $this->assertEquals($user->id, $reviewLike->user->id);
        $this->assertEquals($review->id, $reviewLike->review->id);
    }
}
