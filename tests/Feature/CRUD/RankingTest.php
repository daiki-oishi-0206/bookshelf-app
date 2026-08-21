<?php

namespace Tests\Feature\CRUD;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Review;

class RankingTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_レビュー平均評価のTOP10書籍が降順で表示される(): void
    {
        $books = Book::factory(11)->create();

        $ratings = [5, 4, 3, 2, 1, 5, 4, 3, 2, 1, 1];

        foreach ($books as $index => $book) {
            Review::factory()->create([
                'book_id' => $book->id,
                'rating' => $ratings[$index],
            ]);
        }

        $response = $this->get('/ranking');

        $response->assertStatus(200);

        $response->assertSeeInOrder([
            $books[0]->title, // 5
            $books[5]->title, // 5
            $books[1]->title, // 4
            $books[6]->title, // 4
            $books[2]->title, // 3
            $books[7]->title, // 3
            $books[3]->title, // 2
            $books[8]->title, // 2
            $books[4]->title, // 1
            $books[9]->title, // 1
        ]);

        $response->assertDontSee($books[10]->title);
    }

    public function test_レビューがない書籍はランキングに表示されない(): void
    {
        $bookWithReview = Book::factory()->create([
            'title' => 'レビューあり',
        ]);

        $bookWithoutReview = Book::factory()->create([
            'title' => 'レビューなし',
        ]);

        Review::factory()->create([
            'book_id' => $bookWithReview->id,
            'rating' => 5,
        ]);

        $response = $this->get('/ranking');

        $response->assertStatus(200);

        $response->assertSee('レビューあり');
        $response->assertDontSee('レビューなし');
    }


}
