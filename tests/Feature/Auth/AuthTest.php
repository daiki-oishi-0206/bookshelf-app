<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function validRegistrationData(): array
    {
        return [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];
    }

    private function validLoginData(): array
    {
        return [
            'email' => 'test@example.com',
            'password' => 'password',
        ];
    }

    public function test_正しい情報を入力して会員登録(): void
    {
        $data = $this->validRegistrationData();

        $response = $this->post('/register', $data);

        $response->assertRedirect();

        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
        ]);
    }

    public function test_必須項目を未入力で会員登録(): void
    {
        $data = $this->validRegistrationData();

        $data['name'] = '';
        $data['email'] = '';
        $data['password'] = '';
        $data['password_confirmation'] = '';

        $response = $this->post('/register', $data);

        $response->assertSessionHasErrors([
            'name',
            'email',
            'password',
        ]);
    }

    public function test_メールアドレスの形式が不正な状態で登録(): void
    {
        $data = $this->validRegistrationData();

        $data['email'] = 'invalid-email';

        $response = $this->post('/register', $data);

        $response->assertSessionHasErrors([
            'email',
        ]);
    }

    public function test_登録済みのメールアドレスで会員登録(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $data = $this->validRegistrationData();

        $response = $this->post('/register', $data);

        $response->assertSessionHasErrors([
            'email',
        ]);
    }

    public function test_メールアドレス・パスワードの文字数制限を超えて登録(): void
    {
        $data = $this->validRegistrationData();

        $data['email'] = str_repeat('a', 256) . '@example.com';
        $data['password'] = str_repeat('a', 256);
        $data['password_confirmation'] = str_repeat('a', 256);

        $response = $this->post('/register', $data);

        $response->assertSessionHasErrors([
            'email',
            'password',
        ]);
    }

    public function test_パスワードの文字数が不足した状態で登録(): void
    {
        $data = $this->validRegistrationData();

        $data['password'] = '1234567';
        $data['password_confirmation'] = '1234567';

        $response = $this->post('/register', $data);

        $response->assertSessionHasErrors([
            'password',
        ]);
    }

    public function test_正しいメールアドレスとパスワードでログイン(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $data = $this->validLoginData();

        $response = $this->post('/login', $data);

        $response->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
    }

    public function test_メールアドレスとパスワードを未入力でログイン(): void
    {
        $data = $this->validLoginData();

        $data['email'] = '';
        $data['password'] = '';

        $response = $this->post('/login', $data);

        $response->assertSessionHasErrors([
            'email',
            'password',
        ]);
    }

    public function test_メールアドレスの形式が不正な状態でログイン(): void
    {
        $data = $this->validLoginData();

        $data['email'] = 'invalid-email';

        $response = $this->post('/login', $data);

        $response->assertSessionHasErrors([
            'email',
        ]);
    }

    public function test_メールアドレス・パスワードの文字数制限を超えてログイン(): void
    {
        $data = $this->validLoginData();

        $data['email'] = str_repeat('a', 256) . '@example.com';
        $data['password'] = str_repeat('a', 256);

        $response = $this->post('/login', $data);

        $response->assertSessionHasErrors([
            'email',
            'password',
        ]);
    }

    public function test_パスワードの文字数が不足した状態でログイン(): void
    {
        $data = $this->validLoginData();

        $data['password'] = '1234567';

        $response = $this->post('/login', $data);

        $response->assertSessionHasErrors([
            'password',
        ]);
    }

    public function test_存在しないメールアドレスでログイン(): void
    {
        $data = $this->validLoginData();

        $data['email'] = 'notfound@example.com';

        $response = $this->post('/login', $data);

        $response->assertSessionHasErrors([
            'email',
        ]);

        $this->assertGuest();
    }

    public function test_パスワードが間違っている状態でログイン(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $data = $this->validLoginData();

        $data['password'] = 'wrong-password';

        $response = $this->post('/login', $data);

        $response->assertSessionHasErrors([
            'email',
        ]);

        $this->assertGuest();
    }

    public function test_ログイン状態でログアウト(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response = $this->actingAs($user)
            ->post('/logout');

        $response->assertRedirect('/login');

        $this->assertGuest();

        $response = $this->get('/favorites');

        $response->assertRedirect('/login');
    }
}
