<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $books = Book::all();

        $comments = [
            1 => 'あまりおすすめできない内容でした。',
            2 => '少し物足りなさを感じる内容でした。',
            3 => '読みやすく、普通に楽しめる内容でした。',
            4 => '面白く、参考になる内容でした。',
            5 => 'とても面白く、最後まで楽しく読めました。',
        ];

        foreach ($books as $book) {
            $reviewCount = rand(2, 4);

            for ($i = 0; $i < $reviewCount; $i++) {
                $rating = rand(1, 5);

                Review::create([
                    'user_id' => $users->random()->id,
                    'book_id' => $book->id,
                    'rating' => $rating,
                    'comment' => $comments[$rating],
                ]);
            }
        }

    }
}

