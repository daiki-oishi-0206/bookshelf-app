<?php

namespace Tests\Feature\CRUD;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Genre;
use App\Models\Book;

class GenreTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_ジャンル名を入力してジャンルを登録できる(): void
    {
        $user = User::factory()->create();

        $data = [
            'name' => '小説',
        ];

        $response = $this->actingAs($user)->post('/genres', $data);

        $response->assertRedirect('/genres');

        $this->assertDatabaseHas('genres', [
            'name' => '小説',
        ]);
    }

    public function test_ジャンル登録時のバリデーション(): void
    {
        $user = User::factory()->create();
        Genre::factory()->create([
            'name' => 'ビジネス',
        ]);

        $validData = [
            'name' => '小説',
        ];

        $testCase = [
            '必須項目未入力' => [
                'data' => [
                    'name' => '',
                ],
                'errors' => [
                    'name',
                ],
            ],

            '文字超過' => [
                'data' => [
                    'name' => str_repeat('a', 256),
                ],
                'errors' => [
                    'name',
                ],
            ],

            '登録済みジャンル登録' => [
                'data' => [
                    'name' => 'ビジネス',
                ],
                'errors' => [
                    'name',
                ],
            ],
        ];

        foreach ($testCase as $case) {
            $data = array_merge($validData, $case['data']);

            $response = $this->actingAs($user)->post('/genres', $data);

            $response->assertSessionHasErrors($case['errors']);
        }
    }

    public function test_未ログインでジャンルを登録できない(): void
    {
        $data = [
            'name' => '小説',
        ];

        $response = $this->post('/genres', $data);

        $response->assertRedirect('/login');

        $this->assertDatabaseMissing('genres', [
            'name' => '小説',
        ]);
    }

    public function test_ジャンル名を変更して編集完了できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create([
            'name' => '小説'
        ]);

        $data = [
            'name' => 'ビジネス',
        ];

        $response = $this->actingAs($user)->put("/genres/{$genre->id}", $data);

        $response->assertRedirect('/genres');

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => 'ビジネス',
        ]);
    }

    public function test_現在のジャンル名を維持して編集完了できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create([
            'name' => '小説'
        ]);

        $data = [
            'name' => '小説',
        ];

        $response = $this->actingAs($user)->put("/genres/{$genre->id}", $data);

        $response->assertRedirect('/genres');

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => '小説',
        ]);
    }



    public function test_ジャンル編集時のバリデーション(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create([
            'name' => 'ビジネス',
        ]);
        Genre::factory()->create([
            'name' => '技術書',
        ]);

        $validData = [
            'name' => '小説',
        ];

        $testCase = [
            '必須項目未入力' => [
                'data' => [
                    'name' => '',
                ],
                'errors' => [
                    'name',
                ],
            ],

            '文字超過' => [
                'data' => [
                    'name' => str_repeat('a', 256),
                ],
                'errors' => [
                    'name',
                ],
            ],

            '登録済みジャンル名に変更' => [
                'data' => [
                    'name' => '技術書',
                ],
                'errors' => [
                    'name',
                ],
            ],
        ];

        foreach ($testCase as $case) {
            $data = array_merge($validData, $case['data']);

            $response = $this->actingAs($user)->put("/genres/{$genre->id}", $data);

            $response->assertSessionHasErrors($case['errors']);
        }
    }

    public function test_未ログインでジャンルを編集できない(): void
    {
        $genre = Genre::factory()->create([
            'name' => '小説'
        ]);
        $data = [
            'name' => 'ビジネス',
        ];

        $response = $this->put("/genres/{$genre->id}", $data);

        $response->assertRedirect('/login');

        $this->assertDatabaseMissing('genres', [
            'name' => 'ビジネス',
        ]);
    }

    public function test_関連書籍がないジャンルを削除できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create([
            'name' => '小説'
        ]);

        $response = $this->actingAs($user)->delete("/genres/{$genre->id}");

        $response->assertRedirect('/genres');

        $this->assertDatabaseMissing('genres', [
            'id' => $genre->id,
            'name' => '小説',
        ]);
    }

    public function test_関連書籍があるジャンルを削除できない(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $genre = Genre::factory()->create([
            'name' => '小説'
        ]);
        $book->genres()->attach($genre->id);

        $response = $this->actingAs($user)->delete("/genres/{$genre->id}");

        $response->assertRedirect('/genres');

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => '小説',
        ]);
    }

    public function test_存在しないジャンルを削除できない(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->delete("/genres/99");

        $response->assertStatus(404);

        $this->assertDatabaseMissing('genres', [
            'id' => 99,
        ]);
    }

    public function test_未ログインでジャンルを削除できない(): void
    {
        $genre = Genre::factory()->create([
            'name' => '小説'
        ]);

        $response = $this->delete("/genres/{$genre->id}");

        $response->assertRedirect('/login');

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => '小説',
        ]);
    }

}
