<?php

namespace Tests\Feature\CRUD;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Genre;
use App\Models\Book;

class BookTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_必須項目を入力して書籍を登録できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $data = [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9781234567890',
            'published_date' => '2026-01-01',
            'description' => null,
            'image_url' => null,
            'genres' => [$genre->id],
        ];

        $response = $this->actingAs($user)->post('/books', $data);

        $response->assertRedirect('/');

        $this->assertDatabaseHas('books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9781234567890',
            'published_date' => '2026-01-01',
            'user_id' => $user->id,
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

        $validData = [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9781234567890',
            'published_date' => '2026-01-01',
            'description' => null,
            'image_url' => null,
            'genres' => [$genre->id],
        ];

        $testCase = [
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

            '文字超過' => [
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

            'ISBN桁数不正' => [
                'data' => [
                    'isbn' => '123456789012',
                ],
                'errors' => [
                    'isbn',
                ],
            ],

            'ISBN数字不正' => [
                'data' => [
                    'isbn' => '1234567890abc',
                ],
                'errors' => [
                    'isbn',
                ],
            ],

            'ISBN重複不正' => [
                'data' => [
                    'isbn' => '9781234567890',
                ],
                'errors' => [
                    'isbn',
                ],
                'createBook' => true,
            ],

            '出版日不正' => [
                'data' => [
                    'published_date' => '2026-02-31',
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

        foreach($testCase as $case){
            $data = array_merge($validData, $case['data']);

            if(!empty($case['createBook'])){
                Book::factory()->create([
                    'isbn' => '9781234567890',
                ]);
            }
            
            $response = $this->actingAs($user)->post('/books', $data);

            $response->assertSessionHasErrors($case['errors']);
        }
    }

    public function test_未ログインで書籍を登録できない(): void
    {
        $data = [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9781234567890',
            'published_date' => '2026-01-01',
            'description' => null,
            'image_url' => null,
            'genres' => [],
        ];

        $response = $this->post('/books', $data);

        $response->assertRedirect('/login');

        $this->assertDatabaseMissing('books', [
            'title' => 'テスト書籍',
            'isbn' => '9781234567890',
        ]);
    }


    public function test_必須項目を入力して書籍を更新できる(): void
    {
        $user = User::factory()->create();

        $genre = Genre::factory()->create([
            'name' => '小説',
        ]);

        $updateGenre = Genre::factory()->create([
            'name' => 'ビジネス',
        ]);

        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '変更前の書籍',
            'author' => '変更前の著者',
            'isbn' => '9780000000001',
            'published_date' => '2025-01-01',
        ]);

        $book->genres()->attach($genre->id);

        $data = [
            'title' => '変更後の書籍',
            'author' => '変更後の著者',
            'isbn' => '9781234567890',
            'published_date' => '2026-01-01',
            'description' => null,
            'image_url' => null,
            'genres' => [$updateGenre->id],
        ];

        $response = $this->actingAs($user)
            ->put("/books/{$book->id}", $data);

        $response->assertRedirect("/books/{$book->id}");

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '変更後の書籍',
            'author' => '変更後の著者',
            'isbn' => '9781234567890',
            'published_date' => '2026-01-01',
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $updateGenre->id,
        ]);
    }

    public function test_更新対象自身のISBNを維持して書籍を更新できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
            'isbn' => '9780000000001',
        ]);

        $book->genres()->attach($genre->id);

        $data = [
            'title' => '更新後の書籍',
            'author' => '更新後の著者',
            'isbn' => '9780000000001',
            'published_date' => '2026-01-01',
            'genres' => [$genre->id],
        ];

        $response = $this->actingAs($user)
            ->put("/books/{$book->id}", $data);

        $response->assertSessionDoesntHaveErrors();

        $response->assertRedirect("/books/{$book->id}");

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'isbn' => '9780000000001',
            'title' => '更新後の書籍',
            'published_date' => '2026-01-01',
        ]);
    }

    public function test_書籍更新時のバリデーション(): void
    {
        $user = User::factory()->create();

        $genre = Genre::factory()->create([
            'name' => '小説',
        ]);

        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '変更前の書籍',
            'author' => '変更前の著者',
            'isbn' => '9780000000001',
            'published_date' => '2025-01-01',
        ]);

        $book->genres()->attach($genre->id);

        $validData = [
            'title' => '更新後の書籍',
            'author' => '更新後の著者',
            'isbn' => '9781234567890',
            'published_date' => '2026-01-01',
            'description' => null,
            'image_url' => null,
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

            '文字数超過' => [
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

            'ISBN桁数不正' => [
                'data' => [
                    'isbn' => '123456789012',
                ],
                'errors' => [
                    'isbn',
                ],
            ],

            'ISBN数字不正' => [
                'data' => [
                    'isbn' => '1234567890abc',
                ],
                'errors' => [
                    'isbn',
                ],
            ],

            '他の書籍とISBN重複' => [
                'data' => [
                    'isbn' => '9781111111111',
                ],
                'errors' => [
                    'isbn',
                ],
                'createBook' => true,
            ],

            '出版日不正' => [
                'data' => [
                    'published_date' => '2026-02-31',
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

            if (!empty($case['createBook'])) {
                Book::factory()->create([
                    'isbn' => '9781111111111',
                ]);
            }

            $response = $this->actingAs($user)
                ->put("/books/{$book->id}", $data);

            $response->assertSessionHasErrors($case['errors']);
        }
    }


    public function test_未ログインで書籍を更新できない(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $data = [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'isbn' => $book->isbn,
            'published_date' => '2026-02-01',
            'description' => null,
            'image_url' => null,
            'genres' => [],
        ];

        $response = $this->put("/books/{$book->id}", $data);

        $response->assertRedirect('/login');

        $this->assertDatabaseMissing('books', [
            'title' => '更新後タイトル',
        ]);
    }

    public function test_自分が登録した書籍を削除できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);
        $book->genres()->attach($genre->id);

        $response = $this->actingAs($user)
            ->delete("/books/{$book->id}");

        $response->assertRedirect('/');

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);

        $this->assertDatabaseMissing('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);
    }

    public function test_他ユーザーが登録した書籍を削除できない(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $userA->id,
        ]);
        $book->genres()->attach($genre->id);

        $response = $this->actingAs($userB)
            ->delete("/books/{$book->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
        ]);

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);
    }

    public function test_存在しない書籍を削除できない(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->delete("/books/99");

        $response->assertStatus(404);

        $this->assertDatabaseMissing('books', [
            'id' => 99,
        ]);
    }

    public function test_未ログインで書籍を削除できない(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);
        $book->genres()->attach($genre->id);

        $response = $this->delete("/books/{$book->id}");

        $response->assertRedirect('/login');

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
        ]);

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);
    }

}