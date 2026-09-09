<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewLikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reviews = Review::all();
        $users = User::all();

        foreach ($reviews as $review) {
            $likeUsers = $users
                ->where('id', '!=', $review->user_id)
                ->random(rand(0, 3));

            $likeUserIds = $likeUsers->pluck('id');

            $review->likedByUsers()->syncWithoutDetaching($likeUserIds);
        }
    }
}
