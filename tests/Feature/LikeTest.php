<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class LikeTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_未ログインでお気に入り一覧画面にアクセスするとログイン画面へリダイレクトされる(): void
    {
        $response = $this->get('/favorites');

        $response->assertRedirect('/login');
    }

    public function test_ログイン済みでお気に入り一覧画面にアクセスできる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/favorites');

        $response->assertStatus(200);
    }
}
