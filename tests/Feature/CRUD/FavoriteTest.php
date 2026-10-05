<?php

namespace Tests\Feature\CRUD;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Book $book;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->book = Book::factory()->create();
    }

    public function test_お気に入り登録済みの書籍が一覧に表示される(): void
    {
        $this->user->favoriteBooks()->attach($this->book->id);

        $response = $this->actingAs($this->user)
            ->get('/favorites');

        $response->assertStatus(200);
        $response->assertSee($this->book->title);
    }

    public function test_お気に入りがない場合にメッセージが表示される(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/favorites');

        $response->assertStatus(200);
        $response->assertSee('お気に入りに登録された書籍はありません。');
    }

    public function test_書籍をお気に入り登録できる(): void
    {
        $response = $this->actingAs($this->user)
            ->from("/books/{$this->book->id}")
            ->post("/books/{$this->book->id}/favorite");

        $response->assertRedirect("/books/{$this->book->id}");

        $this->assertDatabaseHas('favorites', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);
    }

    public function test_存在しない書籍をお気に入り登録できない(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/books/99/favorite');

        $response->assertStatus(404);
    }

    public function test_未ログインでお気に入り登録できない(): void
    {
        $response = $this->post("/books/{$this->book->id}/favorite");

        $response->assertRedirect('/login');

        $this->assertDatabaseMissing('favorites', [
            'book_id' => $this->book->id,
        ]);
    }

    public function test_書籍をお気に入り解除できる(): void
    {
        $this->user->favoriteBooks()->attach($this->book->id);

        $response = $this->actingAs($this->user)
            ->from("/books/{$this->book->id}")
            ->post("/books/{$this->book->id}/favorite");

        $response->assertRedirect("/books/{$this->book->id}");

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);
    }

    public function test_未ログインでお気に入り解除できない(): void
    {
        $this->user->favoriteBooks()->attach($this->book->id);

        $response = $this->post("/books/{$this->book->id}/favorite");

        $response->assertRedirect('/login');

        $this->assertDatabaseHas('favorites', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);
    }
}
