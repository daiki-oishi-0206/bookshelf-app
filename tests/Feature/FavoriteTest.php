<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\Review;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_未ログイン状態でレビュー編集画面にアクセスするとログイン画面にリダイレクトされる(): void
    {
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'book_id' => $book->id,
        ]);

        $response = $this->get("/reviews/{$review->id}/edit");

        $response->assertRedirect('/login');
    }

    public function test_ログイン状態でレビュー編集画面にアクセスできる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);
        $review = Review::factory()->create([
            'book_id' => $book->id,
        ]);

        $response = $this->actingAs($user)->get("/reviews/{$review->id}/edit");

        $response->assertStatus(200);
    }
}
