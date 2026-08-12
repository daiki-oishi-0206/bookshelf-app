<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Book;
use App\Models\User;
use App\Models\Review;
use App\Models\ReviewLike;

class ReviewTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic unit test example.
     */
    public function test_Reviewモデルが正常に保存できる(): void
    {
        $review = Review::factory()->create();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
        ]);
    }

    public function test_Reviewモデルのリレーションが正しく取得できる(): void
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
