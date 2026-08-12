<?php

namespace Tests\Unit;

use App\Models\Favorite;
use Tests\TestCase;
use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic unit test example.
     */
    public function test_Favoriteモデルが正常に保存できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $favorite = Favorite::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $this->assertDatabaseHas('favorites', [
            'id' => $favorite->id,
        ]);
    }

    public function test_Favoriteモデルのリレーションが正しく取得できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $favorite = Favorite::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $this->assertEquals($user->id, $favorite->user->id);
        $this->assertEquals($book->id, $favorite->book->id);
    }
}
