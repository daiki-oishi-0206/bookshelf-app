<?php

namespace Tests\Unit\Models;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic unit test example.
     */
    public function test_reading_planモデルの正常動作(): void
    {
        $readingPlan = ReadingPlan::factory()->create();

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
        ]);
    }

    public function test_reading_planモデルのリレーション(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $this->assertEquals($user->id, $readingPlan->user->id);
        $this->assertEquals($book->id, $readingPlan->book->id);
    }

    public function test_reading_planモデルのscope(): void
    {

        ReadingPlan::factory()->create([
            'status' => ReadingPlanStatus::Reading,
        ]);

        ReadingPlan::factory()->create([
            'status' => ReadingPlanStatus::Completed,
        ]);

        $readingPlans = ReadingPlan::reading()->get();
        $completedPlans = ReadingPlan::completed()->get();

        $this->assertCount(1, $readingPlans);
        $this->assertEquals(
            ReadingPlanStatus::Reading,
            $readingPlans->first()->status
        );

        $this->assertCount(1, $completedPlans);
        $this->assertEquals(
            ReadingPlanStatus::Completed,
            $completedPlans->first()->status
        );
    }
}
