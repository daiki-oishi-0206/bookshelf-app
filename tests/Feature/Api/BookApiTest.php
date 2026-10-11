<?php

namespace Tests\Feature\Api;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Genre $genre;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->genre = Genre::factory()->create();
    }

    private function validBookData(int $genreId): array
    {
        return [
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'isbn' => '9781234567891',
            'published_date' => '2025-01-01',
            'description' => 'Laravelの入門書です',
            'image_url' => 'https://example.com/book.jpg',
            'genres' => [$genreId],
        ];
    }

    public function test_書籍一覧を取得(): void
    {
        Book::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/books');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'links',
            'meta' => [
                'current_page',
                'last_page',
                'per_page',
                'total',
            ],
        ]);
    }

    public function test_キーワードを指定して書籍一覧を取得(): void
    {
        Book::factory()->create([
            'title' => 'Laravel入門',
        ]);

        Book::factory()->create([
            'title' => 'PHP入門',
        ]);

        $response = $this->getJson('/api/v1/books?keyword=Laravel');

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'title' => 'Laravel入門',
        ]);
        $response->assertJsonMissing([
            'title' => 'PHP入門',
        ]);
    }

    public function test_ジャンルIDを指定して書籍一覧を取得(): void
    {
        $book = Book::factory()->create();
        $book->genres()->attach($this->genre->id);

        $otherGenre = Genre::factory()->create([
            'name' => '他のジャンル',
        ]);

        $otherBook = Book::factory()->create();
        $otherBook->genres()->attach($otherGenre->id);

        $response = $this->getJson(
            "/api/v1/books?genre_id={$this->genre->id}"
        );

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $book->id,
        ]);
        $response->assertJsonMissing([
            'id' => $otherBook->id,
        ]);
    }

    public function test_ページネーションを指定して書籍一覧を取得(): void
    {
        Book::factory()->count(15)->create();

        $response = $this->getJson(
            '/api/v1/books?page=2&per_page=5'
        );

        $response->assertStatus(200);
        $response->assertJsonPath('meta.current_page', 2);
        $response->assertJsonPath('meta.per_page', 5);
    }

    public function test_キーワードの文字数制限を超えて書籍一覧を取得(): void
    {
        $keyword = str_repeat('a', 256);

        $response = $this->getJson(
            '/api/v1/books?keyword=' . $keyword
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'keyword',
        ]);
    }

    public function test_存在しないジャンルIDを指定して書籍一覧を取得(): void
    {
        $response = $this->getJson(
            '/api/v1/books?genre_id=99999'
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'genre_id',
        ]);
    }

    public function test_不正なページ番号を指定して書籍一覧を取得(): void
    {
        $response = $this->getJson(
            '/api/v1/books?page=0'
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'page',
        ]);
    }

    public function test_不正な1ページあたりの件数を指定して書籍一覧を取得(): void
    {
        $response = $this->getJson(
            '/api/v1/books?per_page=4'
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'per_page',
        ]);
    }

    public function test_書籍詳細を取得(): void
    {
        $book = Book::factory()->create();

        $response = $this->getJson(
            "/api/v1/books/{$book->id}"
        );

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $book->id,
            'title' => $book->title,
        ]);
    }

    public function test_存在しない書籍の詳細を取得(): void
    {
        $response = $this->getJson('/api/v1/books/99999');

        $response->assertStatus(404);
    }


    public function test_正しい情報で書籍を登録(): void
    {
        $data = $this->validBookData($this->genre->id);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/books', $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('books', [
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'isbn' => '9781234567891',
            'published_date' => '2025-01-01',
            'user_id' => $this->user->id,
        ]);

        $book = Book::where('isbn', '9781234567891')->first();

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $this->genre->id,
        ]);
    }

    public function test_必須項目を未入力で書籍を登録(): void
    {
        $data = $this->validBookData($this->genre->id);

        $data['title'] = '';
        $data['author'] = '';
        $data['isbn'] = '';
        $data['published_date'] = '';
        $data['genres'] = [];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/books', $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'title',
            'author',
            'isbn',
            'published_date',
            'genres',
        ]);
    }

    public function test_文字数制限を超えて書籍を登録(): void
    {
        $data = $this->validBookData($this->genre->id);

        $data['title'] = str_repeat('a', 256);
        $data['author'] = str_repeat('a', 256);
        $data['description'] = str_repeat('a', 1001);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/books', $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'title',
            'author',
            'description',
        ]);
    }

    public function test_ISBN13の桁数が不正な状態で書籍を登録(): void
    {
        $data = $this->validBookData($this->genre->id);

        $data['isbn'] = '123456789012';

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/books', $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'isbn',
        ]);
    }

    public function test_ISBN13が数字以外の値で書籍を登録(): void
    {
        $data = $this->validBookData($this->genre->id);

        $data['isbn'] = '97812345678ab';

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/books', $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'isbn',
        ]);
    }

    public function test_登録済みのISBN13で書籍を登録(): void
    {
        Book::factory()->create([
            'isbn' => '9781234567890',
        ]);

        $data = $this->validBookData($this->genre->id);

        $data['isbn'] = '9781234567890';

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/books', $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'isbn',
        ]);
    }

    public function test_出版日に不正な値を指定して書籍を登録(): void
    {
        $data = $this->validBookData($this->genre->id);

        $data['published_date'] = '2025-02-30';

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/books', $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'published_date',
        ]);
    }

    public function test_画像URLに不正な値を指定して書籍を登録(): void
    {
        $data = $this->validBookData($this->genre->id);

        $data['image_url'] = 'invalid-url';

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/books', $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'image_url',
        ]);
    }


    public function test_正しい情報で書籍を更新(): void
    {
        $book = Book::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
            'published_date' => '2020-01-01',
        ]);

        $book->genres()->attach($this->genre->id);

        $newGenre = Genre::factory()->create([
            'name' => '新しいジャンル',
        ]);

        $data = $this->validBookData($newGenre->id);

        $data['title'] = 'Laravel実践入門';
        $data['author'] = '鈴木一郎';
        $data['isbn'] = '9781234567891';
        $data['published_date'] = '2021-01-01';

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/books/{$book->id}", $data);

        $response->assertStatus(200);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => 'Laravel実践入門',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2021-01-01',
        ]);

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $newGenre->id,
        ]);
    }

    public function test_必須項目を未入力で書籍を更新(): void
    {
        $book = Book::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $book->genres()->attach($this->genre->id);

        $data = $this->validBookData($this->genre->id);

        $data['title'] = '';
        $data['author'] = '';
        $data['isbn'] = '';
        $data['published_date'] = '';
        $data['genres'] = [];

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/books/{$book->id}", $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'title',
            'author',
            'isbn',
            'published_date',
            'genres',
        ]);
    }

    public function test_文字数制限を超えて書籍を更新(): void
    {
        $book = Book::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $book->genres()->attach($this->genre->id);

        $data = $this->validBookData($this->genre->id);

        $data['title'] = str_repeat('a', 256);
        $data['author'] = str_repeat('a', 256);
        $data['description'] = str_repeat('a', 1001);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/books/{$book->id}", $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'title',
            'author',
            'description',
        ]);
    }

    public function test_ISBN13の桁数が不正な状態で書籍を更新(): void
    {
        $book = Book::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $book->genres()->attach($this->genre->id);

        $data = $this->validBookData($this->genre->id);

        $data['isbn'] = '123456789012';

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/books/{$book->id}", $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'isbn',
        ]);
    }

    public function test_ISBN13が数字以外の値で書籍を更新(): void
    {
        $book = Book::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $book->genres()->attach($this->genre->id);

        $data = $this->validBookData($this->genre->id);

        $data['isbn'] = '97812345678ab';

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/books/{$book->id}", $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'isbn',
        ]);
    }

    public function test_他の書籍と同じISBN13で書籍を更新(): void
    {
        $book = Book::factory()->create([
            'user_id' => $this->user->id,
            'isbn' => '9781234567890',
        ]);

        $book->genres()->attach($this->genre->id);

        $otherBook = Book::factory()->create([
            'isbn' => '9781234567891',
        ]);

        $data = $this->validBookData($this->genre->id);

        $data['isbn'] = $otherBook->isbn;

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/books/{$book->id}", $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'isbn',
        ]);
    }

    public function test_自身のISBN13を維持して書籍を更新(): void
    {
        $book = Book::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Laravel入門',
            'isbn' => '9781234567890',
        ]);

        $book->genres()->attach($this->genre->id);

        $data = $this->validBookData($this->genre->id);

        $data['title'] = 'Laravel実践入門';
        $data['author'] = '鈴木一郎';
        $data['isbn'] = '9781234567890';

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/books/{$book->id}", $data);

        $response->assertStatus(200);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => 'Laravel実践入門',
            'author' => '鈴木一郎',
            'isbn' => '9781234567890',
        ]);
    }

    public function test_出版日に不正な値を指定して書籍を更新(): void
    {
        $book = Book::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $book->genres()->attach($this->genre->id);

        $data = $this->validBookData($this->genre->id);

        $data['published_date'] = '2025-02-30';

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/books/{$book->id}", $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'published_date',
        ]);
    }

    public function test_画像URLに不正な値を指定して書籍を更新(): void
    {
        $book = Book::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $book->genres()->attach($this->genre->id);

        $data = $this->validBookData($this->genre->id);

        $data['image_url'] = 'invalid-url';

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/books/{$book->id}", $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'image_url',
        ]);
    }

    public function test_存在しない書籍を更新(): void
    {
        $data = $this->validBookData($this->genre->id);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson('/api/v1/books/99999', $data);

        $response->assertStatus(404);

        $response->assertJson([
            'error' => '書籍が見つかりませんでした',
        ]);
    }

    public function test_書籍を削除(): void
    {
        $book = Book::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/books/{$book->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);
    }

    public function test_存在しない書籍を削除(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson('/api/v1/books/99999');

        $response->assertStatus(404);

        $response->assertJson([
            'error' => '書籍が見つかりませんでした',
        ]);
    }

    public function test_未認証で書籍を登録(): void
    {
        $response = $this->postJson('/api/v1/books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '2026-09-28',
            'description' => 'テスト',
            'genres' => [$this->genre->id],
        ]);

        $response->assertStatus(401);
    }
}
