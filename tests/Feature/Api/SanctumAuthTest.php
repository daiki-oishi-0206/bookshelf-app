<?php

namespace Tests\Feature\API;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Genre;

class SanctumAuthTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_トークンを使用して書籍を登録(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create([
            'name' => '技術書',
        ]);
        $token = $user->createToken('test-token')->plainTextToken;

        $data = [
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
            'published_date' => '2026-01-01',
            'genres' => [$genre->id],
        ];

        $response = $this->withHeader(
            'Authorization',
            'Bearer ' . $token
        )->postJson('/api/v1/books', $data);

        $response->assertStatus(201);
    }

    public function test_トークンなしで書籍を登録(): void
    {
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

        $response = $this->postJson('/api/v1/books', $data);

        $response->assertStatus(401);
    }
}