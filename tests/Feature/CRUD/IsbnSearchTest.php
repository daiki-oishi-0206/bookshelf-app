<?php

namespace Tests\Feature\CRUD;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;

class IsbnSearchTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_ISBNから書籍情報を取得する(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([
                'totalItems' => 1,
                'items' => [
                    [
                        'volumeInfo' => [
                            'title' => 'テスト書籍',
                            'authors' => ['テスト著者'],
                            'industryIdentifiers' => [
                                [
                                    'type' => 'ISBN_13',
                                    'identifier' => '9784101010014',
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($user)->get("/books/isbn/9784101010014");

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'title' => 'テスト書籍',
        ]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '9784101010014');
        });

    }

    public function test_存在しないISBNで検索する(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([
                'totalItems' => 0,
                'items' => [],
            ], 200),

        ]);

        $response = $this->actingAs($user)->get("/books/isbn/9784101010099");

        $response->assertStatus(404);

    }

    public function test_ISBN検索時にAPIからエラーレスポンスが返る(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([], 500),

        ]);

        $response = $this->actingAs($user)->get("/books/isbn/9784101010014");

        $response->assertStatus(500);

        $response->assertJson([
            'error' => 'Google Books APIとの通信に失敗しました',
        ]);

    }

    public function test_ISBN検索時にAPIへ接続できない(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => function () {
                throw new ConnectionException('Connection failed.');
            },
        ]);

        $response = $this->actingAs($user)->get("/books/isbn/9784101010014");

        $response->assertStatus(500);

        $response->assertJson([
            'error' => 'Google Books APIとの通信に失敗しました',
        ]);

    }

}

