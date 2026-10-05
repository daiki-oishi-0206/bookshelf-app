<?php

namespace Tests\Feature\CRUD;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Genre $genre;
    private Genre $otherGenre;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->genre = Genre::factory()->create([
            'name' => '小説',
        ]);
        $this->otherGenre = Genre::factory()->create([
            'name' => '技術書',
        ]);
    }

    public function test_ジャンル名を入力してジャンルを登録(): void
    {
        $data = [
            'name' => 'ビジネス',
        ];

        $response = $this->actingAs($this->user)
            ->post('/genres', $data);

        $response->assertRedirect('/genres');

        $this->assertDatabaseHas('genres', [
            'name' => 'ビジネス',
        ]);
    }

    public function test_ジャンル名を未入力で登録(): void
    {
        $data = [
            'name' => '',
        ];

        $response = $this->actingAs($this->user)
            ->post('/genres', $data);

        $response->assertSessionHasErrors([
            'name',
        ]);
    }

    public function test_ジャンル名の文字数制限を超えて登録(): void
    {
        $data = [
            'name' => str_repeat('a', 256),
        ];

        $response = $this->actingAs($this->user)
            ->post('/genres', $data);

        $response->assertSessionHasErrors([
            'name',
        ]);
    }

    public function test_登録済みのジャンル名で登録(): void
    {
        $data = [
            'name' => '小説',
        ];

        $response = $this->actingAs($this->user)
            ->post('/genres', $data);

        $response->assertSessionHasErrors([
            'name',
        ]);
    }

    public function test_未ログインでジャンルを登録(): void
    {
        $data = [
            'name' => 'ビジネス',
        ];

        $response = $this->post('/genres', $data);

        $response->assertRedirect('/login');

        $this->assertDatabaseMissing('genres', [
            'name' => 'ビジネス',
        ]);
    }


    public function test_ジャンル名を変更して編集(): void
    {
        $data = [
            'name' => 'ビジネス',
        ];

        $response = $this->actingAs($this->user)
            ->put("/genres/{$this->genre->id}", $data);

        $response->assertRedirect('/genres');

        $this->assertDatabaseHas('genres', [
            'id' => $this->genre->id,
            'name' => 'ビジネス',
        ]);
    }

    public function test_現在のジャンル名を維持して編集(): void
    {
        $data = [
            'name' => $this->genre->name,
        ];

        $response = $this->actingAs($this->user)
            ->put("/genres/{$this->genre->id}", $data);

        $response->assertRedirect('/genres');

        $this->assertDatabaseHas('genres', [
            'id' => $this->genre->id,
            'name' => $this->genre->name,
        ]);
    }

    public function test_ジャンル名を未入力で編集(): void
    {
        $data = [
            'name' => '',
        ];

        $response = $this->actingAs($this->user)
            ->put("/genres/{$this->genre->id}", $data);

        $response->assertSessionHasErrors([
            'name',
        ]);
    }

    public function test_ジャンル名の文字数制限を超えて編集(): void
    {
        $data = [
            'name' => str_repeat('a', 256),
        ];

        $response = $this->actingAs($this->user)
            ->put("/genres/{$this->genre->id}", $data);

        $response->assertSessionHasErrors([
            'name',
        ]);
    }

    public function test_他のジャンルと重複するジャンル名で編集(): void
    {
        $data = [
            'name' => $this->otherGenre->name,
        ];

        $response = $this->actingAs($this->user)
            ->put("/genres/{$this->genre->id}", $data);

        $response->assertSessionHasErrors([
            'name',
        ]);
    }

    public function test_未ログインでジャンルを編集(): void
    {
        $data = [
            'name' => 'ビジネス',
        ];

        $response = $this->put("/genres/{$this->genre->id}", $data);

        $response->assertRedirect('/login');

        $this->assertDatabaseMissing('genres', [
            'name' => 'ビジネス',
        ]);

        $this->assertDatabaseHas('genres', [
            'id' => $this->genre->id,
            'name' => '小説',
        ]);
    }

    public function test_関連書籍がないジャンルを削除(): void
    {
        $response = $this->actingAs($this->user)
            ->delete("/genres/{$this->genre->id}");

        $response->assertRedirect('/genres');

        $this->assertDatabaseMissing('genres', [
            'id' => $this->genre->id,
            'name' => '小説',
        ]);
    }

    public function test_関連書籍があるジャンルを削除(): void
    {
        $book = Book::factory()->create();

        $book->genres()->attach($this->genre->id);

        $response = $this->actingAs($this->user)
            ->delete("/genres/{$this->genre->id}");

        $response->assertRedirect('/genres');

        $this->assertDatabaseHas('genres', [
            'id' => $this->genre->id,
            'name' => '小説',
        ]);
    }

    public function test_存在しないジャンルを削除(): void
    {
        $response = $this->actingAs($this->user)
            ->delete('/genres/99');

        $response->assertStatus(404);

        $this->assertDatabaseMissing('genres', [
            'id' => 99,
        ]);
    }

    public function test_未ログインでジャンルを削除(): void
    {
        $response = $this->delete("/genres/{$this->genre->id}");

        $response->assertRedirect('/login');

        $this->assertDatabaseHas('genres', [
            'id' => $this->genre->id,
            'name' => '小説',
        ]);
    }
}
