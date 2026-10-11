<?php

namespace Tests\Feature\Access;

use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_未ログインでジャンル管理画面にアクセス(): void
    {
        $response = $this->get('/genres');

        $response->assertRedirect('/login');
    }

    public function test_ログイン済みでジャンル管理画面にアクセス(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/genres');

        $response->assertStatus(200);
    }

    public function test_未ログインでジャンル詳細画面にアクセス(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->get("/genres/{$genre->id}");

        $response->assertRedirect('/login');
    }

    public function test_ログイン済みでジャンル詳細画面にアクセス(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->get("/genres/{$genre->id}");

        $response->assertStatus(200);
    }

    public function test_未ログインでジャンル登録画面にアクセス(): void
    {
        $response = $this->get('/genres/create');

        $response->assertRedirect('/login');
    }

    public function test_ログイン済みでジャンル登録画面にアクセス(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/genres/create');

        $response->assertStatus(200);
    }

    public function test_未ログインでジャンル編集画面にアクセス(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->get("/genres/{$genre->id}/edit");

        $response->assertRedirect('/login');
    }

    public function test_ログイン済みでジャンル編集画面にアクセス(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->get("/genres/{$genre->id}/edit");

        $response->assertStatus(200);
    }
}
