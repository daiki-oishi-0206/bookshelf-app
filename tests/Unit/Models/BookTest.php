<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Book;
use App\Models\Favorite;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;

class BookTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic unit test example.
     */
    public function test_Bookモデルが正常に保存できる(): void
    {
        $book = Book::factory()->create();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
        ]);
    }

    public function test_Bookモデルのリレーションが正しく取得できる(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $review = Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $user->id,
        ]);

        $favorite = Favorite::factory()->create([
            'book_id' => $book->id,
            'user_id' => $user->id,
        ]);

        $genre = Genre::factory()->create();

        $book->genres()->attach($genre->id);

        $this->assertEquals($user->id, $book->user->id);
        $this->assertTrue($book->reviews->contains($review));
        $this->assertTrue($book->favorites->contains($favorite));
        $this->assertTrue($book->genres->contains($genre));
        $this->assertTrue($book->favoriteUsers->contains($user));
    }
}

