<?php

namespace Tests\Feature\Access;

use App\Models\Book;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    public function test_未ログインで書籍一覧画面にアクセスできる(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_未ログインで書籍詳細画面にアクセスできる(): void
    {
        $book = Book::factory()->create();

        $response = $this->get("/books/{$book->id}");

        $response->assertStatus(200);
    }

    public function test_未ログインで書籍登録画面にアクセスするとログイン画面へリダイレクトされる(): void
    {
        $response = $this->get('/books/create');

        $response->assertRedirect('/login');
    }

    public function test_ログイン済みで書籍登録画面にアクセスできる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/books/create');

        $response->assertStatus(200);
    }

    public function test_未ログインで書籍編集画面にアクセスするとログイン画面へリダイレクトされる(): void
    {
        $book = Book::factory()->create();

        $response = $this->get("/books/{$book->id}/edit");

        $response->assertRedirect('/login');
    }

    public function test_ログイン済みで書籍編集画面にアクセスできる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get("/books/{$book->id}/edit");

        $response->assertStatus(200);
    }

    public function test_他ユーザーの書籍編集画面にアクセスすると403が返る(): void
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
