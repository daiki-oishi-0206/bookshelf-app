<?php

namespace Tests\Feature\CRUD;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Book;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_お気に入り登録済みの書籍が一覧に表示される(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $user->favoriteBooks()->attach($book->id);

        $response = $this->actingAs($user)
            ->get('/favorites');

        $response->assertStatus(200);
        $response->assertSee($book->title);
    }

    public function test_お気に入りがない場合にメッセージが表示される(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/favorites');

        $response->assertStatus(200);
        $response->assertSee('お気に入りに登録された書籍はありません。');
    }

    public function test_書籍をお気に入り登録できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)
            ->from("/books/{$book->id}")
            ->post("/books/{$book->id}/favorite");

        $response->assertRedirect("/books/{$book->id}");

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    public function test_存在しない書籍をお気に入り登録できない(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post("/books/99/favorite");

        $response->assertStatus(404);
    }

    public function test_未ログインでお気に入り登録できない(): void
    {
        $book = Book::factory()->create();

        $response = $this->post("/books/{$book->id}/favorite");

        $response->assertRedirect('/login');

        $this->assertDatabaseMissing('favorites', [
            'book_id' => $book->id,
        ]);
    }

    public function test_書籍をお気に入り解除できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $user->favoriteBooks()->attach($book->id);

        $response = $this->actingAs($user)
            ->from("/books/{$book->id}")
            ->post("/books/{$book->id}/favorite");

        $response->assertRedirect("/books/{$book->id}");

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    public function test_未ログインでお気に入り解除できない(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $user->favoriteBooks()->attach($book->id);

        $response = $this->post("/books/{$book->id}/favorite");

        $response->assertRedirect('/login');

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

}
