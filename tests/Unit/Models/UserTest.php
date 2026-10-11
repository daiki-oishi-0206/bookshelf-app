<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use App\Models\Favorite;
use App\Models\Review;
use App\Models\ReviewLike;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic unit test example.
     */
    public function test_Userモデルの正常動作(): void
    {
        $user = User::factory()->create();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
        ]);
    }

    public function test_Userモデルのリレーション(): void
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
