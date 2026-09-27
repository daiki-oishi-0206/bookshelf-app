<?php

namespace Tests\Feature;

use App\Models\ReadingPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpirationBatchTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_読書計画の期日が1日過ぎる(): void
    {
        $user = User::factory()->create();

        $readingPlanA = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => 'reading',
            'target_date' => Carbon::yesterday(),
        ]);

        $readingPlanB = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => 'completed',
            'target_date' => Carbon::yesterday(),
        ]);

        $this->artisan('reading-plans:remind');

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlanA->id,
            'status' => 'overdue',
        ]);

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlanB->id,
            'status' => 'completed',
        ]);
    }
}
