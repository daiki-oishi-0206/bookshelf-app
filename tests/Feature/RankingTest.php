<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RankingTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_未ログインでランキング画面にアクセスできる(): void
    {
        $response = $this->get('/ranking');

        $response->assertStatus(200);
    }
}

// 各ページのアクセステスト実装