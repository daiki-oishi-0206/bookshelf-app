<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Book;
use App\Models\ReadingPlan;
use Carbon\Carbon;

class ReadingPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $books = Book::all();

        ReadingPlan::create([
            'user_id' => $users[0]->id,
            'book_id' => $books[0]->id,
            'status' => 'not_started',
            'target_date' => Carbon::today()->subDays(1),
        ]);
        
        ReadingPlan::create([
            'user_id' => $users[0]->id,
            'book_id' => $books[1]->id,
            'status' => 'reading',
            'target_date' => Carbon::today()->subDays(1),
        ]);

        ReadingPlan::create([
            'user_id' => $users[0]->id,
            'book_id' => $books[2]->id,
            'status' => 'not_started',
            'target_date' => Carbon::today()->addDays(3),
        ]);

        ReadingPlan::create([
            'user_id' => $users[0]->id,
            'book_id' => $books[3]->id,
            'status' => 'reading',
            'target_date' => Carbon::today(),
        ]);

        ReadingPlan::create([
            'user_id' => $users[0]->id,
            'book_id' => $books[4]->id,
            'status' => 'overdue',
            'target_date' => Carbon::today()->subDays(3),
        ]);

        ReadingPlan::create([
            'user_id' => $users[0]->id,
            'book_id' => $books[5]->id,
            'status' => 'not_started',
            'target_date' => Carbon::today()->addDays(1),
        ]);

        ReadingPlan::create([
            'user_id' => $users[0]->id,
            'book_id' => $books[6]->id,
            'status' => 'reading',
            'target_date' => Carbon::today()->addDays(1),
        ]);

        ReadingPlan::create([
            'user_id' => $users[0]->id,
            'book_id' => $books[7]->id,
            'status' => 'completed',
            'target_date' => Carbon::today()->subDays(1),
        ]);

        ReadingPlan::create([
            'user_id' => $users[0]->id,
            'book_id' => $books[7]->id,
            'status' => 'not_started',
            'target_date' => Carbon::today()->addDays(3),
        ]);

        ReadingPlan::create([
            'user_id' => $users[1]->id,
            'book_id' => $books[8]->id,
            'status' => 'not_started',
            'target_date' => Carbon::today()->addDays(3),
        ]);

    }
}

