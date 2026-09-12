<?php

namespace Tests\Unit\Models;

use App\Models\ReadingPlan;
use App\Models\User;
use App\Models\Book;
use App\Enums\ReadingPlanStatus;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReadingPlanTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic unit test example.
     */
    public function test_ReadingPlanモデルの正常動作(): void
    {
        $readingPlan = ReadingPlan::factory()->create();

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
        ]);
    }

    public function test_ReadingPlanモデルのリレーション(): void
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

    public function test_ReadingPlanモデルのscope(): void
    {
        ReadingPlan::factory()->create([
            'status' => ReadingPlanStatus::NotStarted,
        ]);

        ReadingPlan::factory()->create([
            'status' => ReadingPlanStatus::Reading,
        ]);

        ReadingPlan::factory()->create([
            'status' => ReadingPlanStatus::Completed,
        ]);

        $notStartedPlans = ReadingPlan::notStarted()->get();
        $readingPlans = ReadingPlan::reading()->get();
        $completedPlans = ReadingPlan::completed()->get();

        $this->assertCount(1, $notStartedPlans);
        $this->assertEquals(
            ReadingPlanStatus::NotStarted,
            $notStartedPlans->first()->status
        );

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
