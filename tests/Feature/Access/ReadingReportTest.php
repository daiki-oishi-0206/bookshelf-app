<?php

namespace Tests\Feature\Access;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingReportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_未ログインでマイ読書レポート画面にアクセス(): void
    {
        $response = $this->get('/reports');

        $response->assertRedirect('/login');
    }

    public function test_ログイン済みでマイ読書レポート画面にアクセス(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/reports');

        $response->assertStatus(200);
    }
}
