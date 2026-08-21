<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Genre;
use App\Models\Book;

class GenreTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic unit test example.
     */
    public function test_Genreモデルが正常に保存できる(): void
    {
        $genre = Genre::factory()->create();

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
        ]);
    }

    public function test_Genreモデルのリレーションが正しく取得できる(): void
    {
        $book = Book::factory()->create();

        $genre = Genre::factory()->create();

        $book->genres()->attach($genre->id);

        $this->assertTrue($genre->books->contains($book));
    }
}
