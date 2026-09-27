<?php

namespace Tests\Feature\CRUD;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\ReadingPlan;
use Carbon\Carbon;


class ReadingPlanTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_読書計画を作成(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $data = [
            'book_id' => $book->id,
            'target_date' => Carbon::today()->addDays(3),
        ];

        $response = $this->actingAs($user)->post('/reading-plans', $data);

        $response->assertRedirect('/reading-plans');

        $this->assertDatabaseHas('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => 'reading',
            'target_date' => Carbon::today()->addDays(3),
        ]);
    }

    public function test_読書計画一覧を確認(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);
        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => 'reading',
            'target_date' => Carbon::today()->addDays(3),
        ]);

        $response = $this->actingAs($user)->get('/reading-plans');

        $response->assertStatus(200);

        $response->assertSee($book->title);
        $response->assertSee(Carbon::today()->addDays(3)->format('Y-m-d'));
        $response->assertSee('reading');
    }

    public function test_読書計画を削除(): void
    {
        $user = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->delete("/reading-plans/{$readingPlan->id}");

        $response->assertRedirect('/reading-plans');

        $this->assertDatabaseMissing('reading_plans', [
            'id' => $readingPlan->id,
        ]);
    }

    public function test_読書計画を読了にする(): void
    {
        $user = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => 'reading',
        ]);

        $response = $this->actingAs($user)
            ->post("/reading-plans/{$readingPlan->id}/complete");

        $response->assertRedirect('/reading-plans');

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'status' => 'completed',
        ]);
    }

    public function test_読了済みの書籍で読書計画を作成(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => 'completed',
        ]);

        $data = [
            'book_id' => $book->id,
            'target_date' => Carbon::today()->addDays(3),
        ];

        $response = $this->actingAs($user)
            ->post('/reading-plans', $data);

        $response->assertRedirect('/reading-plans');

        $this->assertDatabaseHas('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => Carbon::today()->addDays(3),
            'status' => 'reading',
        ]);
    }

    public function test_必須項目を未入力で読書計画を作成(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/reading-plans', []);

        $response->assertStatus(302);

        $response->assertSessionHasErrors([
            'book_id' => '書籍を選択してください',
            'target_date' => '期日は必須です',
        ]);
    }

    public function test_存在しない書籍を指定して読書計画を作成(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/reading-plans', [
                'book_id' => 999999,
                'target_date' => Carbon::today()->addDays(3),
            ]);

        $response->assertStatus(302);

        $response->assertSessionHasErrors([
            'book_id' => '選択した書籍が存在しません',
        ]);
    }

    public function test_過去の日付で読書計画を作成(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->post('/reading-plans', [
                'book_id' => $book->id,
                'target_date' => Carbon::yesterday(),
            ]);

        $response->assertStatus(302);

        $response->assertSessionHasErrors([
            'target_date' => '期日は今日以降の日付を指定してください',
        ]);
    }

    public function test_無効な日付で読書計画を作成(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->post('/reading-plans', [
                'book_id' => $book->id,
                'target_date' => 'invalid-date',
            ]);

        $response->assertStatus(302);

        $response->assertSessionHasErrors([
            'target_date' => '期日は有効な日付を入力してください',
        ]);
    }

    public function test_進行中の書籍を再度読書計画に登録(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => 'reading',
            'target_date' => Carbon::today()->addDays(3),
        ]);

        $response = $this->actingAs($user)
            ->post('/reading-plans', [
                'book_id' => $book->id,
                'target_date' => Carbon::today()->addDays(7),
            ]);

        $response->assertStatus(302);

        $response->assertSessionHasErrors([
            'book_id' => 'この書籍はすでに読書計画に登録されています',
        ]);
    }

    public function test_期限超過の書籍を再度読書計画に登録(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => 'overdue',
            'target_date' => Carbon::yesterday(),
        ]);

        $response = $this->actingAs($user)
            ->post('/reading-plans', [
                'book_id' => $book->id,
                'target_date' => Carbon::today()->addDays(7),
            ]);

        $response->assertStatus(302);

        $response->assertSessionHasErrors([
            'book_id' => 'この書籍はすでに読書計画に登録されています',
        ]);
    }

    public function test_未ログインで読書計画一覧を確認(): void
    {
        $response = $this->get('/reading-plans');

        $response->assertRedirect('/login');
    }

    public function test_未ログインで読書計画を作成(): void
    {
        $book = Book::factory()->create();

        $response = $this->post('/reading-plans', [
            'book_id' => $book->id,
            'target_date' => Carbon::today()->addDays(3),
        ]);

        $response->assertRedirect('/login');

        $this->assertDatabaseCount('reading_plans', 0);
    }

    public function test_未ログインで読書計画を削除(): void
    {
        $readingPlan = ReadingPlan::factory()->create();

        $response = $this->delete("/reading-plans/{$readingPlan->id}");

        $response->assertRedirect('/login');

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
        ]);
    }

    public function test_他ユーザーの読書計画が一覧に表示されない(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        ReadingPlan::factory()->create([
            'user_id' => $otherUser->id,
            'book_id' => $book->id,
            'status' => 'reading',
        ]);

        $response = $this->actingAs($user)
            ->get('/reading-plans');

        $response->assertStatus(200);

        $response->assertDontSee($book->title);
    }

    public function test_他ユーザーの読書計画を削除(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $otherUser->id,
            'book_id' => $book->id,
        ]);

        $response = $this->actingAs($user)
            ->delete("/reading-plans/{$readingPlan->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
        ]);
    }
}

