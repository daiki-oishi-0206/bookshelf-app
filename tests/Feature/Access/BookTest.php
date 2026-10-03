<?php

namespace Tests\Feature\Access;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    public function test_未ログインで書籍一覧画面にアクセス(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_未ログインで書籍詳細画面にアクセス(): void
    {
        $book = Book::factory()->create();

        $response = $this->get("/books/{$book->id}");

        $response->assertStatus(200);
    }

    public function test_未ログインで書籍登録画面にアクセス(): void
    {
        $response = $this->get('/books/create');

        $response->assertRedirect('/login');
    }

    public function test_ログイン済みで書籍登録画面にアクセス(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/books/create');

        $response->assertStatus(200);
    }

    public function test_未ログインで書籍編集画面にアクセス(): void
    {
        $book = Book::factory()->create();

        $response = $this->get("/books/{$book->id}/edit");

        $response->assertRedirect('/login');
    }

    public function test_ログイン済みで書籍編集画面にアクセス(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get("/books/{$book->id}/edit");

        $response->assertStatus(200);
    }

    public function test_他ユーザーの書籍編集画面にアクセス(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $userA->id,
        ]);

        $response = $this->actingAs($userB)->get("/books/{$book->id}/edit");

        $response->assertStatus(403);
    }
}
