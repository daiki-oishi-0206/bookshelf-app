<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class AuthTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_正しい情報を入力して会員登録できる(): void
    {
        $data = [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->post('/register', $data);

        $response->assertRedirect();

        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
        ]);
    }

    public function test_会員登録時のバリデーション(): void
    {
        User::factory()->create([
            'name' => 'テストユーザーA',
            'email' => 'valid_a@example.com',
        ]);

        $validData = [
            'name' => 'テストユーザーB',
            'email' => 'valid_b@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $testCases = [
            '必須項目未入力' => [
                'data' => [
                    'name' => '',
                    'email' => '',
                    'password' => '',
                    'password_confirmation' => '',
                ],
                'errors' => [
                    'name',
                    'email',
                    'password',
                ],
            ],

            'メールアドレス形式不正' => [
                'data' => [
                    'email' => 'invalid-email',
                ],
                'errors' => [
                    'email',
                ],
            ],

            '登録済みメールアドレス' => [
                'data' => [
                    'email' => 'valid_a@example.com',
                ],
                'errors' => [
                    'email',
                ],
            ],

            'メールアドレス文字数超過' => [
                'data' => [
                    'email' => str_repeat('a', 256) . '@example.com',
                ],
                'errors' => [
                    'email',
                ],
            ],

            'パスワード文字数不足' => [
                'data' => [
                    'password' => '1234567',
                    'password_confirmation' => '1234567',
                ],
                'errors' => [
                    'password',
                ],
            ],

            'パスワード文字数超過' => [
                'data' => [
                    'password' => str_repeat('a', 256),
                    'password_confirmation' => str_repeat('a', 256),
                ],
                'errors' => [
                    'password',
                ],
            ],
        ];

        foreach ($testCases as $case) {
            $data = array_merge($validData, $case['data']);

            $response = $this->post('/register', $data);

            $response->assertSessionHasErrors($case['errors']);
        }
    }

    public function test_正しい情報でログインできる(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $data = [
            'email' => 'test@example.com',
            'password' => 'password',
        ];

        $response = $this->post('/login', $data);

        $response->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
    }

    public function test_ログイン時のバリデーション(): void
    {
        User::factory()->create([
            'email' => 'valid_a@example.com',
            'password' => 'password',
        ]);

        $validData = [
            'email' => 'valid_a@example.com',
            'password' => 'password',
        ];

        $testCases = [
            '必須項目未入力' => [
                'data' => [
                    'email' => '',
                    'password' => '',
                ],
                'errors' => [
                    'email',
                    'password',
                ],
            ],

            'メールアドレス形式不正' => [
                'data' => [
                    'email' => 'invalid-email',
                ],
                'errors' => [
                    'email',
                ],
            ],

            'メールアドレス文字数超過' => [
                'data' => [
                    'email' => str_repeat('a', 256) . '@example.com',
                ],
                'errors' => [
                    'email',
                ],
            ],

            'パスワード文字数不足' => [
                'data' => [
                    'password' => '1234567',
                ],
                'errors' => [
                    'password',
                ],
            ],

            'パスワード文字数超過' => [
                'data' => [
                    'password' => str_repeat('a', 256),
                ],
                'errors' => [
                    'password',
                ],
            ],
        ];

        foreach ($testCases as $case) {
            $data = array_merge($validData, $case['data']);

            $response = $this->post('/login', $data);

            $response->assertSessionHasErrors($case['errors']);
        }
    }

    public function test_存在しないメールアドレスではログインできない(): void
    {
        $data = [
            'email' => 'notfound@example.com',
            'password' => 'password',
        ];

        $response = $this->post('/login', $data);

        $response->assertSessionHasErrors([
            'email',
        ]);

        $this->assertGuest();
    }

    public function test_パスワードが間違っている場合はログインできない(): void
    {
        User::factory()->create([
            'email' => 'valid@example.com',
            'password' => 'password',
        ]);

        $data = [
            'email' => 'valid@example.com',
            'password' => 'wrong-password',
        ];

        $response = $this->post('/login', $data);

        $response->assertSessionHasErrors([
            'email',
        ]);

        $this->assertGuest();
    }

    public function test_ログアウトできる(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/login');

        $this->assertGuest();

        $response = $this->get('/favorites');

        $response->assertRedirect('/login');
    }
}

