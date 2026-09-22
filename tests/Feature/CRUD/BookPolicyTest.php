<?php

namespace Tests\Feature\CRUD;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\Genre;

class BookPolicyTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_自分が登録した書籍は更新できる(): void
    {
        $user = User::factory()->create();

        $genre = Genre::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '変更前の書籍',
            'author' => '変更前の著者',
        ]);

        $book->genres()->attach($genre->id);

        $data = [
            'title' => '変更後の書籍',
            'author' => '変更後の著者',
            'isbn' => null,
            'published_date' => null,
            'description' => null,
            'image_url' => null,
            'genres' => [$genre->id],
        ];

        $response = $this->actingAs($user)
            ->put("/books/{$book->id}", $data);

        $response->assertRedirect("/books/{$book->id}");
    }

    public function test_自分が登録した書籍は削除できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);


        $response = $this->actingAs($user)->delete("/books/{$book->id}");
        $response->assertRedirect("/");
    }

    public function test_他ユーザーが登録した書籍は更新できない(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $genre = Genre::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $otherUser->id,
            'title' => '変更前の書籍',
            'author' => '変更前の著者',
        ]);

        $book->genres()->attach($genre->id);

        $data = [
            'title' => '変更後の書籍',
            'author' => '変更後の著者',
            'isbn' => null,
            'published_date' => null,
            'description' => null,
            'image_url' => null,
            'genres' => [$genre->id],
        ];

        $response = $this->actingAs($user)
            ->put("/books/{$book->id}", $data);

        $response->assertForbidden();
    }

    public function test_他ユーザーが登録した書籍は削除できない(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user)->delete("/books/{$book->id}");

        $response->assertForbidden();
    }
}

// ⬆️完了