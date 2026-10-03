<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use App\Models\Review;
use App\Models\ReviewLike;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic unit test example.
     */
    public function test_Reviewモデルの正常動作(): void
    {
        $review = Review::factory()->create();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
        ]);
    }

    public function test_Reviewモデルのリレーション(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
        $reviewLike = ReviewLike::factory()->create([
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);

        $this->assertEquals($user->id, $review->user->id);
        $this->assertEquals($book->id, $review->book->id);
        $this->assertTrue($review->likes->contains($reviewLike));
        $this->assertTrue($review->likedByUsers->contains($user));
    }
}
