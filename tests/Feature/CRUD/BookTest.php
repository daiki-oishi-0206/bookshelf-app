<?php

namespace Tests\Feature\CRUD;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Genre $genre;
    private Book $book;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->genre = Genre::factory()->create();
        $this->book = Book::factory()->create([
            'user_id' => $this->user->id,
            'isbn' => '9780000000001',
        ]);
        $this->book->genres()->attach($this->genre->id);
    }

    private function validBookData(): array
    {
        return [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => null,
            'published_date' => null,
            'description' => null,
            'image_url' => null,
            'genres' => [$this->genre->id],
        ];
    }

    public function test_必須項目を入力して書籍を登録(): void
    {
        $data = $this->validBookData();

        $response = $this->actingAs($this->user)
            ->post('/books', $data);

        $response->assertRedirect('/');

        $this->assertDatabaseHas('books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'user_id' => $this->user->id,
        ]);

        $book = Book::where('title', 'テスト書籍')->first();
        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $this->genre->id,
        ]);
    }

    public function test_必須項目を未入力で登録(): void
    {
        $data = $this->validBookData();

        $data['title'] = '';
        $data['author'] = '';
        $data['isbn'] = '';
        $data['published_date'] = '';
        $data['genres'] = [];

        $response = $this->actingAs($this->user)
            ->post('/books', $data);

        $response->assertSessionHasErrors([
            'title',
            'author',
            'genres',
        ]);
    }

    public function test_文字数制限を超えて登録(): void
    {
        $data = $this->validBookData();

        $data['title'] = str_repeat('a', 256);
        $data['author'] = str_repeat('a', 256);
        $data['description'] = str_repeat('a', 1001);

        $response = $this->actingAs($this->user)
            ->post('/books', $data);

        $response->assertSessionHasErrors([
            'title',
            'author',
            'description',
        ]);
    }

    public function test_ISBN13の桁数を12桁で登録(): void
    {
        $data = $this->validBookData();
        $data['isbn'] = '123456789012';

        $response = $this->actingAs($this->user)
            ->post('/books', $data);

        $response->assertSessionHasErrors(['isbn']);
    }

    public function test_ISBN13の桁数を14桁で登録(): void
    {
        $data = $this->validBookData();
        $data['isbn'] = '12345678901234';

        $response = $this->actingAs($this->user)
            ->post('/books', $data);

        $response->assertSessionHasErrors(['isbn']);
    }

    public function test_ISBN13を数字以外で登録(): void
    {
        $data = $this->validBookData();
        $data['isbn'] = '1234567890abc';

        $response = $this->actingAs($this->user)
            ->post('/books', $data);

        $response->assertSessionHasErrors(['isbn']);
    }

    public function test_登録済みのISBN13で登録(): void
    {
        $isbn = '9781234567890';

        Book::factory()->create([
            'isbn' => $isbn,
        ]);

        $data = $this->validBookData();
        $data['isbn'] = $isbn;

        $response = $this->actingAs($this->user)
            ->post('/books', $data);

        $response->assertSessionHasErrors(['isbn']);
    }

    public function test_出版日に不正な値を入力して登録(): void
    {
        $data = $this->validBookData();
        $data['published_date'] = '2026-02-31';

        $response = $this->actingAs($this->user)
            ->post('/books', $data);

        $response->assertSessionHasErrors(['published_date']);
    }

    public function test_画像URLに不正な値を入力して登録(): void
    {
        $data = $this->validBookData();
        $data['image_url'] = 'invalid-url';

        $response = $this->actingAs($this->user)
            ->post('/books', $data);

        $response->assertSessionHasErrors(['image_url']);
    }

    public function test_未ログインで書籍を登録(): void
    {
        $data = $this->validBookData();

        $response = $this->post('/books', $data);

        $response->assertRedirect('/login');
    }


    public function test_必須項目を入力して書籍を更新(): void
    {
        $data = $this->validBookData();

        $data['title'] = '変更後の書籍';
        $data['author'] = '変更後の著者';

        $response = $this->actingAs($this->user)
            ->put("/books/{$this->book->id}", $data);

        $response->assertRedirect("/books/{$this->book->id}");

        $this->assertDatabaseHas('books', [
            'id' => $this->book->id,
            'title' => '変更後の書籍',
            'author' => '変更後の著者',
            'user_id' => $this->user->id,
        ]);

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $this->book->id,
            'genre_id' => $this->genre->id,
        ]);
    }

    public function test_更新対象自身のISBN13を維持して更新(): void
    {
        $data = $this->validBookData();

        $data['title'] = '更新後の書籍';
        $data['author'] = '更新後の著者';
        $data['isbn'] = '9780000000001';
        $data['published_date'] = '2026-01-01';

        $response = $this->actingAs($this->user)
            ->put("/books/{$this->book->id}", $data);

        $response->assertSessionDoesntHaveErrors();
        $response->assertRedirect("/books/{$this->book->id}");

        $this->assertDatabaseHas('books', [
            'id' => $this->book->id,
            'isbn' => '9780000000001',
            'title' => '更新後の書籍',
            'published_date' => '2026-01-01',
        ]);
    }

    public function test_必須項目を未入力で更新(): void
    {
        $data = $this->validBookData();

        $data['title'] = '';
        $data['author'] = '';
        $data['genres'] = [];

        $response = $this->actingAs($this->user)
            ->put("/books/{$this->book->id}", $data);

        $response->assertSessionHasErrors([
            'title',
            'author',
            'genres',
        ]);
    }

    public function test_文字数制限を超えて更新(): void
    {
        $data = $this->validBookData();

        $data['title'] = str_repeat('a', 256);
        $data['author'] = str_repeat('a', 256);
        $data['description'] = str_repeat('a', 1001);

        $response = $this->actingAs($this->user)
            ->put("/books/{$this->book->id}", $data);

        $response->assertSessionHasErrors([
            'title',
            'author',
            'description',
        ]);
    }

    public function test_ISBN13の桁数を12桁で更新(): void
    {
        $data = $this->validBookData();
        $data['isbn'] = '123456789012';

        $response = $this->actingAs($this->user)
            ->put("/books/{$this->book->id}", $data);

        $response->assertSessionHasErrors(['isbn']);
    }

    public function test_ISBN13の桁数を14桁で更新(): void
    {
        $data = $this->validBookData();
        $data['isbn'] = '12345678901234';

        $response = $this->actingAs($this->user)
            ->put("/books/{$this->book->id}", $data);

        $response->assertSessionHasErrors(['isbn']);
    }

    public function test_ISBN13を数字以外で更新(): void
    {
        $data = $this->validBookData();
        $data['isbn'] = '1234567890abc';

        $response = $this->actingAs($this->user)
            ->put("/books/{$this->book->id}", $data);

        $response->assertSessionHasErrors(['isbn']);
    }

    public function test_他の書籍と同じISBN13で更新(): void
    {
        $isbn = '9781111111111';

        Book::factory()->create([
            'isbn' => $isbn,
        ]);

        $data = $this->validBookData();
        $data['isbn'] = $isbn;

        $response = $this->actingAs($this->user)
            ->put("/books/{$this->book->id}", $data);

        $response->assertSessionHasErrors(['isbn']);
    }

    public function test_出版日に不正な値を入力して更新(): void
    {
        $data = $this->validBookData();
        $data['published_date'] = '2026-02-31';

        $response = $this->actingAs($this->user)
            ->put("/books/{$this->book->id}", $data);

        $response->assertSessionHasErrors(['published_date']);
    }

    public function test_画像URLに不正な値を入力して更新(): void
    {
        $data = $this->validBookData();
        $data['image_url'] = 'invalid-url';

        $response = $this->actingAs($this->user)
            ->put("/books/{$this->book->id}", $data);

        $response->assertSessionHasErrors(['image_url']);
    }

    public function test_未ログインで書籍を更新(): void
    {
        $data = $this->validBookData();

        $data['title'] = '変更後の書籍';
        $data['author'] = '変更後の著者';

        $response = $this->put("/books/{$this->book->id}", $data);

        $response->assertRedirect('/login');

        $this->assertDatabaseMissing('books', [
            'title' => '変更後の書籍',
            'author' => '変更後の著者',
        ]);
    }

    public function test_自分が登録した書籍を削除(): void
    {
        $response = $this->actingAs($this->user)
            ->delete("/books/{$this->book->id}");

        $response->assertRedirect('/');

        $this->assertDatabaseMissing('books', [
            'id' => $this->book->id,
        ]);

        $this->assertDatabaseMissing('book_genre', [
            'book_id' => $this->book->id,
            'genre_id' => $this->genre->id,
        ]);
    }

    public function test_他ユーザーが登録した書籍を削除(): void
    {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
            ->delete("/books/{$this->book->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('books', [
            'id' => $this->book->id,
        ]);

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $this->book->id,
            'genre_id' => $this->genre->id,
        ]);
    }

    public function test_存在しない書籍を削除(): void
    {
        $response = $this->actingAs($this->user)
            ->delete('/books/99');

        $response->assertStatus(404);

        $this->assertDatabaseMissing('books', [
            'id' => 99,
        ]);
    }

    public function test_未ログインで書籍を削除(): void
    {
        $response = $this->delete("/books/{$this->book->id}");

        $response->assertRedirect('/login');

        $this->assertDatabaseHas('books', [
            'id' => $this->book->id,
        ]);

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $this->book->id,
            'genre_id' => $this->genre->id,
        ]);
    }
}
