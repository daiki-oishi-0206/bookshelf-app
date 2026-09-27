<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\Review;
use App\Models\Genre;

class ReadingReportTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_レビュー登録済みの読書レポートを確認する(): void
    {
        $user = User::factory()->create();

        $genreA = Genre::factory()->create([
            'name' => '小説',
        ]);
        $genreB = Genre::factory()->create([
            'name' => 'ビジネス',
        ]);
        $genreC = Genre::factory()->create([
            'name' => '技術書',
        ]);

        $bookA = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'テスト書籍A',
        ]);
        $bookB = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'テスト書籍B',
        ]);
        $bookC = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'テスト書籍C',
        ]);

        $bookA->genres()->attach($genreA->id);
        $bookB->genres()->attach($genreB->id);
        $bookC->genres()->attach($genreC->id);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $bookA->id,
            'rating' => 5,
        ]);
        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $bookB->id,
            'rating' => 4,
        ]);
        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $bookC->id,
            'rating' => 3,
        ]);

        $reviews = $user->reviews;

        $response = $this->actingAs($user)->get('/reports');

        $response->assertStatus(200);

        $response->assertSee($reviews->count());
        $response->assertSee($reviews->unique('book_id')->count());
        $response->assertSee($reviews->avg('rating') ?? 0);

        $response->assertSeeInOrder([
            '★',
            '0件',
            '★★',
            '0件',
            '★★★',
            '1件',
            '★★★★',
            '1件',
            '★★★★★',
            '1件',
        ]);

        $response->assertSeeInOrder([
            $bookA->title,
            $bookB->title,
        ]);

        $response->assertSeeInOrder([
            $genreA->name,
            $genreB->name,
            $genreC->name,
        ]);

    }

    public function test_レビューがない状態で読書レポートを確認(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/reports');

        $response->assertStatus(200);

        $response->assertSee('4星以上の書籍がありません');
        $response->assertSee('ジャンルが設定された書籍のレビューがありません');
    }

    public function test_高評価書籍から書籍詳細を確認(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);
        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
        ]);

        $response = $this->actingAs($user)->get('/reports');

        $response->assertStatus(200);
        $response->assertSee($book->title);
        $response->assertSee(route('books.show', $book->id));

        $response = $this->actingAs($user)->get("/books/{$book->id}");

        $response->assertStatus(200);
        $response->assertSeeInOrder([
            $book->title,
            $book->author,
            $book->isbn,
        ]);
    }

    public function test_ジャンル別評価傾向からジャンル詳細を確認(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $genre = Genre::factory()->create();
        $book->genres()->attach($genre->id);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
        ]);


        $response = $this->actingAs($user)->get('/reports');

        $response->assertStatus(200);
        $response->assertSee($genre->name);
        $response->assertSee(route('genres.show', $genre->id));

        $response = $this->actingAs($user)->get("/genres/{$genre->id}");

        $response->assertStatus(200);
        $response->assertSeeInOrder([
            $book->title,
            $book->author,
            $genre->name,
        ]);
    }

    public function test_他ユーザーのレビューがある状態で読書レポートを確認(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $bookA = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'テスト書籍A',
        ]);
        $bookB = Book::factory()->create([
            'user_id' => $otherUser->id,
            'title' => 'テスト書籍B',
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $bookA->id,
            'rating' => 5,
        ]);
        Review::factory()->create([
            'user_id' => $otherUser->id,
            'book_id' => $bookB->id,
            'rating' => 5,
        ]);

        $response = $this->actingAs($user)->get('/reports');

        $response->assertSee($bookA->title);
        $response->assertDontSee($bookB->title);
}

}