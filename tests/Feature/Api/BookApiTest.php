<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Genre;
use App\Models\User;

class BookApiTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_書籍一覧を取得できる(): void
    {
        Book::factory(3)->create();

        $response = $this->getJson('/api/v1/books');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'author',
                    'genres',
                    'average_rating',
                    'review_count',
                ],
            ],
            'links',
            'meta',
        ]);
    }

    public function test_キーワードを指定して書籍一覧を取得できる(): void
    {
        Book::factory()->create([
            'title' => 'Laravel入門',
            'author' => '山田太郎',
        ]);

        Book::factory()->create([
            'title' => 'PHP入門',
            'author' => '鈴木花子',
        ]);

        $response = $this->getJson('/api/v1/books?keyword=Laravel');

        $response->assertStatus(200);

        $response->assertJsonCount(1, 'data');

        $response->assertJsonPath('data.0.title', 'Laravel入門');

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'author',
                    'genres',
                    'average_rating',
                    'review_count',
                ],
            ],
        ]);
    }

    public function test_ジャンルIDを指定して書籍一覧を取得できる(): void
    {
        $genre1 = Genre::factory()->create([
            'name' => '小説',
        ]);

        $genre2 = Genre::factory()->create([
            'name' => '技術書',
        ]);

        $book1 = Book::factory()->create([
            'title' => '小説の本',
        ]);

        $book2 = Book::factory()->create([
            'title' => '技術書の本',
        ]);

        $book1->genres()->attach($genre1);
        $book2->genres()->attach($genre2);

        $response = $this->getJson(
            "/api/v1/books?genre_id={$genre1->id}"
        );

        $response->assertStatus(200);

        $response->assertJsonCount(1, 'data');

        $response->assertJsonFragment([
            'title' => '小説の本',
        ]);

        $response->assertJsonMissing([
            'title' => '技術書の本',
        ]);

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'author',
                    'genres',
                    'average_rating',
                    'review_count',
                ],
            ],
        ]);
    }

    public function test_ページネーションを指定して書籍一覧を取得できる(): void
    {
        Book::factory()->count(10)->create();

        $response = $this->getJson(
            '/api/v1/books?page=2&per_page=5'
        );

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'author',
                    'genres',
                    'average_rating',
                    'review_count',
                ],
            ],
            'links',
            'meta',
        ]);

        $response->assertJsonPath('meta.current_page', 2);
        $response->assertJsonPath('meta.per_page', 5);

        $response->assertJsonCount(5, 'data');
    }

    public function test_書籍一覧取得時のバリデーション(): void
    {
        $validData = [
            'keyword' => 'Laravel',
            'genre_id' => Genre::factory()->create()->id,
            'page' => 1,
            'per_page' => 10,
        ];

        $testCases = [
            'キーワード文字数超過' => [
                'data' => [
                    'keyword' => str_repeat('a', 256),
                ],
                'errors' => [
                    'keyword',
                ],
            ],

            '存在しないジャンルID' => [
                'data' => [
                    'genre_id' => 99999,
                ],
                'errors' => [
                    'genre_id',
                ],
            ],

            'ページ番号が0' => [
                'data' => [
                    'page' => 0,
                ],
                'errors' => [
                    'page',
                ],
            ],

            'ページ番号が負の値' => [
                'data' => [
                    'page' => -1,
                ],
                'errors' => [
                    'page',
                ],
            ],

            '1ページあたりの件数が4以下' => [
                'data' => [
                    'per_page' => 4,
                ],
                'errors' => [
                    'per_page',
                ],
            ],

            '1ページあたりの件数が101以上' => [
                'data' => [
                    'per_page' => 101,
                ],
                'errors' => [
                    'per_page',
                ],
            ],
        ];

        foreach ($testCases as $case) {
            $data = array_merge($validData, $case['data']);

            $response = $this->getJson('/api/v1/books?' . http_build_query($data));

            $response->assertStatus(422);

            $response->assertJsonValidationErrors($case['errors']);
        }
    }

    public function test_書籍詳細を取得できる(): void
    {
        $book = Book::factory()->create([
            'title' => 'Laravel入門',
            'author' => '山田太郎',
        ]);

        $response = $this->getJson("/api/v1/books/{$book->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'author',
                'isbn',
                'published_date',
                'description',
                'genres',
                'reviews',
            ],
        ]);

        $response->assertJsonFragment([
            'id' => $book->id,
            'title' => 'Laravel入門',
            'author' => '山田太郎',
        ]);
    }

    public function test_存在しない書籍の詳細を取得できない(): void
    {
        $response = $this->getJson('/api/v1/books/99999');

        $response->assertStatus(404);

        $response->assertJson([
            'error' => '書籍が見つかりませんでした。',
        ]);
    }

    public function test_正しい情報で書籍を登録できる(): void
    {
        $user = User::factory()->create();

        $genre = Genre::factory()->create([
            'name' => '技術書',
        ]);

        $data = [
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
            'published_date' => '2026-01-01',
            'genres' => [$genre->id],
        ];

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/books', $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('books', [
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
            'published_date' => '2026-01-01',
        ]);

        $book = Book::where('isbn', '9781234567890')->first();

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);
    }

    public function test_書籍登録時のバリデーション(): void
    {
        $user = User::factory()->create();

        $genre = Genre::factory()->create();

        Book::factory()->create([
            'isbn' => '9781234567890',
        ]);

        $validData = [
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'isbn' => '9781234567891',
            'published_date' => '2025-01-01',
            'description' => 'Laravelの入門書です。',
            'image_url' => 'https://example.com/book.jpg',
            'genres' => [$genre->id],
        ];

        $testCases = [
            '必須項目未入力' => [
                'data' => [
                    'title' => '',
                    'author' => '',
                    'isbn' => '',
                    'published_date' => '',
                    'genres' => [],
                ],
                'errors' => [
                    'title',
                    'author',
                    'isbn',
                    'published_date',
                    'genres',
                ],
            ],

            '文字数制限超過' => [
                'data' => [
                    'title' => str_repeat('a', 256),
                    'author' => str_repeat('a', 256),
                    'description' => str_repeat('a', 1001),
                ],
                'errors' => [
                    'title',
                    'author',
                    'description',
                ],
            ],

            'ISBN13桁数不足' => [
                'data' => [
                    'isbn' => '123456789012',
                ],
                'errors' => [
                    'isbn',
                ],
            ],

            'ISBN13桁数超過' => [
                'data' => [
                    'isbn' => '12345678901234',
                ],
                'errors' => [
                    'isbn',
                ],
            ],

            'ISBN数字以外' => [
                'data' => [
                    'isbn' => '97812345678ab',
                ],
                'errors' => [
                    'isbn',
                ],
            ],

            '登録済みISBN' => [
                'data' => [
                    'isbn' => '9781234567890',
                ],
                'errors' => [
                    'isbn',
                ],
            ],

            '出版日不正' => [
                'data' => [
                    'published_date' => '2025-02-30',
                ],
                'errors' => [
                    'published_date',
                ],
            ],

            '画像URL不正' => [
                'data' => [
                    'image_url' => 'invalid-url',
                ],
                'errors' => [
                    'image_url',
                ],
            ],
        ];

        foreach ($testCases as $case) {
            $data = array_merge($validData, $case['data']);

            $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/books', $data);

            $response->assertStatus(422);

            $response->assertJsonValidationErrors($case['errors']);
        }
    }

    public function test_正しい情報で書籍を更新できる(): void
    {
        $user = User::factory()->create();

        $genre1 = Genre::factory()->create([
            'name' => '小説',
        ]);

        $genre2 = Genre::factory()->create([
            'name' => '技術書',
        ]);

        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
            'published_date' => '2020-01-01',
        ]);

        $book->genres()->attach($genre1);

        $data = [
            'title' => 'Laravel実践入門',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2021-01-01',
            'genres' => [$genre2->id],
        ];

        $response = $this->actingAs($user, 'sanctum')->putJson(
            "/api/v1/books/{$book->id}",
            $data
        );

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
            'genre_id' => $genre2->id,
        ]);
    }

    public function test_自身のISBN13を維持して書籍を更新できる(): void
    {
        $user = User::factory()->create();

        $genre = Genre::factory()->create([
            'name' => '技術書',
        ]);

        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
            'published_date' => '2020-01-01',
        ]);

        $book->genres()->attach($genre);

        $data = [
            'title' => 'Laravel実践入門',
            'author' => '鈴木一郎',
            'isbn' => '9781234567890',
            'published_date' => '2021-01-01',
            'genres' => [$genre->id],
        ];

        $response = $this->actingAs($user, 'sanctum')->putJson(
            "/api/v1/books/{$book->id}",
            $data
        );

        $response->assertStatus(200);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => 'Laravel実践入門',
            'author' => '鈴木一郎',
            'isbn' => '9781234567890',
            'published_date' => '2021-01-01',
        ]);
    }

    public function test_書籍更新時のバリデーション(): void
    {
        $user = User::factory()->create();

        $genre = Genre::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
            'published_date' => '2020-01-01',
        ]);

        $book->genres()->attach($genre);

        $otherBook = Book::factory()->create([
            'isbn' => '9781234567891',
        ]);

        $validData = [
            'title' => 'Laravel実践入門',
            'author' => '鈴木一郎',
            'isbn' => '9781234567892',
            'published_date' => '2021-01-01',
            'description' => 'Laravelの実践書です。',
            'image_url' => 'https://example.com/book.jpg',
            'genres' => [$genre->id],
        ];

        $testCases = [
            '必須項目未入力' => [
                'data' => [
                    'title' => '',
                    'author' => '',
                    'isbn' => '',
                    'published_date' => '',
                    'genres' => [],
                ],
                'errors' => [
                    'title',
                    'author',
                    'isbn',
                    'published_date',
                    'genres',
                ],
            ],

            '文字数制限超過' => [
                'data' => [
                    'title' => str_repeat('a', 256),
                    'author' => str_repeat('a', 256),
                    'description' => str_repeat('a', 1001),
                ],
                'errors' => [
                    'title',
                    'author',
                    'description',
                ],
            ],

            'ISBN13桁数不足' => [
                'data' => [
                    'isbn' => '123456789012',
                ],
                'errors' => [
                    'isbn',
                ],
            ],

            'ISBN13桁数超過' => [
                'data' => [
                    'isbn' => '12345678901234',
                ],
                'errors' => [
                    'isbn',
                ],
            ],

            'ISBN数字以外' => [
                'data' => [
                    'isbn' => '97812345678ab',
                ],
                'errors' => [
                    'isbn',
                ],
            ],

            '他の書籍と同じISBN' => [
                'data' => [
                    'isbn' => $otherBook->isbn,
                ],
                'errors' => [
                    'isbn',
                ],
            ],

            '出版日不正' => [
                'data' => [
                    'published_date' => '2025-02-30',
                ],
                'errors' => [
                    'published_date',
                ],
            ],

            '画像URL不正' => [
                'data' => [
                    'image_url' => 'invalid-url',
                ],
                'errors' => [
                    'image_url',
                ],
            ],
        ];

        foreach ($testCases as $case) {
            $data = array_merge($validData, $case['data']);

            $response = $this->actingAs($user, 'sanctum')->putJson(
                "/api/v1/books/{$book->id}",
                $data
            );

            $response->assertStatus(422);

            $response->assertJsonValidationErrors($case['errors']);
        }
    }

    public function test_存在しない書籍を更新できない(): void
    {
        $user = User::factory()->create();

        $genre = Genre::factory()->create();

        $data = [
            'title' => 'Laravel実践入門',
            'author' => '鈴木一郎',
            'isbn' => '9781234567892',
            'published_date' => '2021-01-01',
            'description' => 'Laravelの実践書です。',
            'image_url' => 'https://example.com/book.jpg',
            'genres' => [$genre->id],
        ];

        $response = $this->actingAs($user, 'sanctum')->putJson(
            '/api/v1/books/99999',
            $data
        );

        $response->assertStatus(404);

        $response->assertJson([
            'error' => '書籍が見つかりませんでした。',
        ]);
    }

    public function test_書籍を削除できる(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')->deleteJson(
            "/api/v1/books/{$book->id}"
        );

        $response->assertStatus(200);

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);
    }

    public function test_存在しない書籍を削除できない(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->deleteJson('/api/v1/books/99999');

        $response->assertStatus(404);

        $response->assertJson([
            'error' => '書籍が見つかりませんでした。',
        ]);
    }
}
