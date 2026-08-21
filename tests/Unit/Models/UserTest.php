<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Book;
use App\Models\Favorite;
use App\Models\Review;
use App\Models\ReviewLike;
use App\Models\User;

class UserTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic unit test example.
     */
    public function test_Userモデルが正常に保存できる(): void
    {
        $user = User::factory()->create();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
        ]);
    }

    public function test_Userモデルのリレーションが正しく取得できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);
        $favorite = Favorite::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $reviewLike = ReviewLike::factory()->create([
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);

        $this->assertTrue($user->books->contains($book));
        $this->assertTrue($user->favorites->contains($favorite));
        $this->assertTrue($user->reviews->contains($review));
        $this->assertTrue($user->likes->contains($reviewLike));
        $this->assertTrue($user->favoriteBooks->contains($book));
        $this->assertTrue($user->likedReviews->contains($review));
    }
}
