<?php

namespace Tests\Feature\CRUD;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Review;

class SortTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_新しい順に書籍を並び替える(): void
    {
        $bookA = Book::factory()->create([
            'created_at' => '2021-01-01 11:11:11',
        ]);
        $bookB = Book::factory()->create([
            'created_at' => '2022-02-02 22:22:22' ,
        ]);

        $response = $this->get('/?sort=newest');

        $response->assertStatus(200);

        $response->assertSeeInOrder([
            $bookB->title,
            $bookA->title,
        ]);
    }

    public function test_古い順に書籍を並び替える(): void
    {
        $bookA = Book::factory()->create([
            'created_at' => '2021-01-01 11:11:11',
        ]);
        $bookB = Book::factory()->create([
            'created_at' => '2022-02-02 22:22:22' ,
        ]);

        $response = $this->get('/?sort=oldest');

        $response->assertStatus(200);

        $response->assertSeeInOrder([
            $bookA->title,
            $bookB->title,
        ]);
    }

    public function test_評価の高い順に書籍を並び替える(): void
    {
        $bookA = Book::factory()->create();
        $bookB = Book::factory()->create();

        Review::factory()->create([
            'book_id' => $bookA->id,
            'rating' => 5,
        ]);
        Review::factory()->create([
            'book_id' => $bookB->id,
            'rating' => 1,
        ]);

        $response = $this->get('/?sort=rating');

        $response->assertStatus(200);

        $response->assertSeeInOrder([
            $bookA->title,
            $bookB->title,
        ]);
    }

    public function test_タイトル順に書籍を並び替える(): void
    {
        $bookA = Book::factory()->create([
            'title' => 'テスト書籍A',
        ]);
        $bookB = Book::factory()->create([
            'title' => 'テスト書籍B',
        ]);

        $response = $this->get('/?sort=title');

        $response->assertStatus(200);

        $response->assertSeeInOrder([
            $bookA->title,
            $bookB->title,
        ]);
    }

    public function test_無効なソート順を指定して検索する(): void
    {
        $response = $this->get('/?sort=XXX');

        $response->assertStatus(302);

        $response->assertSessionHasErrors('sort');

    }

}
