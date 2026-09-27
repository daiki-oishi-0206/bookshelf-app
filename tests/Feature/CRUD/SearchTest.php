<?php

namespace Tests\Feature\CRUD;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Genre;

class SearchTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_キーワードの部分一致で書籍を検索(): void
    {
        Book::factory()->create([
            'title' => 'テスト書籍',
        ]);

        Book::factory()->create([
            'title' => '別の書籍',
        ]);

        $response = $this->get('/?keyword=テスト');

        $response->assertSee('テスト書籍');

        $response->assertStatus(200);

        $response->assertDontSee('別の書籍');
    }

    public function test_ジャンルを指定して書籍をフィルタ(): void
    {
        $genre = Genre::factory()->create([
            'name' => '小説',
        ]);
        $otherGenre = Genre::factory()->create([
            'name' => 'ビジネス',
        ]);

        $book = Book::factory()->create();
        $otherBook = Book::factory()->create();

        $book->genres()->attach($genre->id);
        $otherBook->genres()->attach($otherGenre->id);

        $response = $this->get("/?genre={$genre->id}");

        $response->assertStatus(200);
        $response->assertSee($book->title);
        $response->assertDontSee($otherBook->title);

    }

    public function test_キーワードとジャンルを指定して書籍を検索(): void
    {
        $genre = Genre::factory()->create([
            'name' => '小説',
        ]);
        $otherGenre = Genre::factory()->create([
            'name' => 'ビジネス',
        ]);
        
        $book = Book::factory()->create([
            'title' => 'テスト書籍',
        ]);
        $otherBook = Book::factory()->create([
            'title' => '別の書籍',
        ]);

        $book->genres()->attach($genre->id);
        $otherBook->genres()->attach($otherGenre->id);

        $response = $this->get("/?keyword=テスト&genre={$genre->id}");

        $response->assertSee($book->title);
        $response->assertDontSee($otherBook->title);
        $response->assertStatus(200);
    }

    public function test_存在しない検索ワードで書籍を検索(): void
    {
        Book::factory()->create([
            'title' => 'テスト書籍A',
        ]);

        Book::factory()->create([
            'title' => 'テスト書籍B',
        ]);

        $response = $this->get('/?keyword=テスト書籍C');

        $response->assertDontSee('テスト書籍A');
        $response->assertDontSee('テスト書籍B');

        $response->assertStatus(200);
    }

    public function test_検索条件を入力せずに検索(): void
    {
        Book::factory()->create([
            'title' => 'テスト書籍A',
        ]);

        Book::factory()->create([
            'title' => 'テスト書籍B',
        ]);

        $response = $this->get('/');

        $response->assertSee('テスト書籍A');
        $response->assertSee('テスト書籍B');

        $response->assertStatus(200);
    }
    public function test_検索結果を次のページに切り替える(): void
    {
        Book::factory()->create([
            'title' => 'テスト書籍A',
        ]);
        Book::factory()->create([
            'title' => 'テスト書籍B',
        ]);
        Book::factory()->create([
            'title' => 'テスト書籍C',
        ]);
        Book::factory()->create([
            'title' => 'テスト書籍D',
        ]);
        Book::factory()->create([
            'title' => 'テスト書籍E',
        ]);
        Book::factory()->create([
            'title' => 'テスト書籍F',
        ]);
        Book::factory()->create([
            'title' => 'テスト書籍G',
        ]);
        Book::factory()->create([
            'title' => 'テスト書籍H',
        ]);
        Book::factory()->create([
            'title' => 'テスト書籍I',
        ]);
        Book::factory()->create([
            'title' => 'テスト書籍J',
        ]);
        Book::factory()->create([
            'title' => 'テスト書籍K',
        ]);
        Book::factory()->create([
            'title' => '書籍L',
        ]);

        $response = $this->get('/?keyword=テスト&page=2');

        $response->assertSee('テスト書籍K');
        $response->assertDontSee('テスト書籍A');
        $response->assertDontSee('書籍L');

        $response->assertStatus(200);

    }

    public function test_キーワードの文字数制限を超えて検索(): void
    {
        $keyword = str_repeat('あ', 256);

        $response = $this->get("/?keyword={$keyword}");

        $response->assertStatus(302);

        $response->assertSessionHasErrors('keyword');
    }

    public function test_不正なジャンルを指定して検索する(): void
    {
        Genre::factory()->create();

        $response = $this->get("/?genre=99");

        $response->assertStatus(302);

        $response->assertSessionHasErrors('genre');
    }

    public function test_不正なページ番号を指定して検索する(): void
    {
        $response = $this->get("/?page=0");

        $response->assertStatus(302);

        $response->assertSessionHasErrors('page');
    }

}

