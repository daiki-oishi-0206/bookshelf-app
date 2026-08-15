<?php

namespace Tests\Feature\CRUD;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Review;
use App\Models\User;
use App\Models\Book;

class ReviewTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_評価を入力してレビューを投稿できる(): void
    {
        
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $data = [
            'rating' => 5,
            'comment' => NULL,
        ];

        $response = $this->actingAs($user)
        ->from("/books/{$book->id}")
        ->post("/books/{$book->id}/review", $data);

        $response->assertRedirect("/books/{$book->id}");

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => NULL,
        ]);
    }

    public function test_評価とコメントを入力してレビューを投稿できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $data = [
            'rating' => 5,
            'comment' => '面白い本でした',
        ];

        $response = $this->actingAs($user)
        ->from("/books/{$book->id}")
        ->post("/books/{$book->id}/review", $data);

        $response->assertRedirect("/books/{$book->id}");

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => '面白い本でした',
        ]);

    }

    public function test_レビュー投稿時のバリデーション(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $validData = [
            'rating' => 5,
            'comment' => '面白い本でした',
        ];

        $testCase = [
            '必須項目未入力' => [
                'data' => [
                    'rating' => '',
                ],
                'errors' => [
                    'rating',
                ],
            ],

            '評価に0入力' => [
                'data' => [
                    'rating' => 0,
                ],
                'errors' => [
                    'rating',
                ],
            ],

            '評価に6以上入力' => [
                'data' => [
                    'rating' => 6,
                ],
                'errors' => [
                    'rating',
                ],
            ],

            '評価に整数以外入力' => [
                'data' => [
                    'rating' => 1.5,
                ],
                'errors' => [
                    'rating',
                ],
            ],

            'コメント文字超過' => [
                'data' => [
                    'comment' => str_repeat('a', 1001),
                ],
                'errors' => [
                    'comment',
                ],
            ],
        ];

        foreach ($testCase as $case) {
            $data = array_merge($validData, $case['data']);

            $response = $this->actingAs($user)->post("/books/{$book->id}/review", $data);

            $response->assertSessionHasErrors($case['errors']);
        }
    }

    public function test_評価を変更してレビューを編集できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 1,
            'comment' => NULL,
        ]);

        $data = [
            'rating' => 5,
            'comment' => NULL,
        ];

        $response = $this->actingAs($user)
            ->from("/books/{$book->id}")
            ->put("/reviews/{$review->id}", $data);

        $response->assertRedirect("/books/{$book->id}");

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => NULL,
        ]);
    }

    public function test_評価とコメントを変更してレビューを編集できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 1,
            'comment' => NULL,
        ]);

        $data = [
            'rating' => 5,
            'comment' => '面白い本でした',
        ];

        $response = $this->actingAs($user)
            ->from("/books/{$book->id}")
            ->put("/reviews/{$review->id}", $data);

        $response->assertRedirect("/books/{$book->id}");

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => '面白い本でした',
        ]);
    }

    public function test_レビュー編集時のバリデーション(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 1,
            'comment' => '面白い本でした',
        ]);

        $validData = [
            'rating' => 5,
            'comment' => '面白い本でした',
        ];

        $testCase = [
            '必須項目未入力' => [
                'data' => [
                    'rating' => '',
                ],
                'errors' => [
                    'rating',
                ],
            ],

            '評価に0入力' => [
                'data' => [
                    'rating' => 0,
                ],
                'errors' => [
                    'rating',
                ],
            ],

            '評価に6以上入力' => [
                'data' => [
                    'rating' => 6,
                ],
                'errors' => [
                    'rating',
                ],
            ],

            '評価に整数以外入力' => [
                'data' => [
                    'rating' => 1.5,
                ],
                'errors' => [
                    'rating',
                ],
            ],

            'コメント文字超過' => [
                'data' => [
                    'comment' => str_repeat('a', 1001),
                ],
                'errors' => [
                    'comment',
                ],
            ],
        ];

        foreach ($testCase as $case) {
            $data = array_merge($validData, $case['data']);

            $response = $this->actingAs($user)->put("/reviews/{$review->id}", $data);

            $response->assertSessionHasErrors($case['errors']);
        }
    }

    public function test_自分が投稿したレビューを削除できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $response = $this->actingAs($user)
            ->delete("/reviews/{$review->id}");

        $response->assertRedirect("/books/{$book->id}");

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);
    }

    public function test_他ユーザーが投稿したレビューを削除できない(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $userA->id,
        ]);
        $review = Review::factory()->create([
            'user_id' => $userA->id,
            'book_id' => $book->id,
        ]);

        $response = $this->actingAs($userB)
            ->delete("/reviews/{$review->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
        ]);
    }

    public function test_存在しないレビューを削除できない(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->delete("/reviews/99");

        $response->assertStatus(404);

        $this->assertDatabaseMissing('reviews', [
            'id' => 99,
        ]);
    }

    public function test_未ログインでレビューを削除できない(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $response = $this->delete("/reviews/{$review->id}");

        $response->assertRedirect('/login');

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
        ]);
    }

}