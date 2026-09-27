<?php

namespace Tests\Feature\Access;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

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
