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

        Review::create([
            'user_id' => $users[0]->id,
            'book_id' => $books[0]->id,
            'rating' => 5,
            'comment' => '独特な語り口が面白く、最後まで楽しく読むことができました。',
        ]);

        Review::create([
            'user_id' => $users[1]->id,
            'book_id' => $books[0]->id,
            'rating' => 4,
            'comment' => '猫の視点から人間社会を描いているところが興味深かったです。',
        ]);

        Review::create([
            'user_id' => $users[2]->id,
            'book_id' => $books[0]->id,
            'rating' => 4,
            'comment' => '昔の作品ですが、ユーモアがあり読みやすい作品でした。',
        ]);

        Review::create([
            'user_id' => $users[1]->id,
            'book_id' => $books[1]->id,
            'rating' => 5,
            'comment' => '人との接し方について考え直すきっかけになりました。',
        ]);

        Review::create([
            'user_id' => $users[2]->id,
            'book_id' => $books[1]->id,
            'rating' => 5,
            'comment' => '仕事や日常生活でも活用できる考え方が多く参考になりました。',
        ]);

        Review::create([
            'user_id' => $users[3]->id,
            'book_id' => $books[1]->id,
            'rating' => 4,
            'comment' => '具体的な例が多く、人間関係について学びやすい内容でした。',
        ]);

        Review::create([
            'user_id' => $users[0]->id,
            'book_id' => $books[2]->id,
            'rating' => 5,
            'comment' => '読みやすいコードを書くための考え方が具体的に理解できました。',
        ]);

        Review::create([
            'user_id' => $users[3]->id,
            'book_id' => $books[2]->id,
            'rating' => 5,
            'comment' => 'プログラミング初心者でもコードの改善方法を学びやすい本でした。',
        ]);

        Review::create([
            'user_id' => $users[4]->id,
            'book_id' => $books[2]->id,
            'rating' => 4,
            'comment' => '普段何気なく書いていたコードを見直すきっかけになりました。',
        ]);

        Review::create([
            'user_id' => $users[1]->id,
            'book_id' => $books[3]->id,
            'rating' => 5,
            'comment' => '自分自身の行動や考え方を見直すきっかけになる本でした。',
        ]);

        Review::create([
            'user_id' => $users[2]->id,
            'book_id' => $books[3]->id,
            'rating' => 4,
            'comment' => '仕事だけでなく日常生活にも活かせる内容が多かったです。',
        ]);

        Review::create([
            'user_id' => $users[4]->id,
            'book_id' => $books[3]->id,
            'rating' => 5,
            'comment' => '長く読み継がれている理由が分かる、学びの多い一冊でした。',
        ]);

        Review::create([
            'user_id' => $users[0]->id,
            'book_id' => $books[4]->id,
            'rating' => 4,
            'comment' => '主人公の真っ直ぐな性格が印象的で、楽しく読むことができました。',
        ]);

        Review::create([
            'user_id' => $users[2]->id,
            'book_id' => $books[4]->id,
            'rating' => 5,
            'comment' => '登場人物の個性が豊かで、物語に引き込まれました。',
        ]);

        Review::create([
            'user_id' => $users[3]->id,
            'book_id' => $books[4]->id,
            'rating' => 4,
            'comment' => 'テンポよく話が進むので、最後まで飽きずに楽しめました。',
        ]);

        Review::create([
            'user_id' => $users[1]->id,
            'book_id' => $books[5]->id,
            'rating' => 5,
            'comment' => '人類の歴史を大きな視点から考えることができ、とても興味深かったです。',
        ]);

        Review::create([
            'user_id' => $users[3]->id,
            'book_id' => $books[5]->id,
            'rating' => 4,
            'comment' => '歴史について新しい視点を持つことができる内容でした。',
        ]);

        Review::create([
            'user_id' => $users[4]->id,
            'book_id' => $books[5]->id,
            'rating' => 5,
            'comment' => '人類がどのように発展してきたのかを考えさせられる一冊でした。',
        ]);

        Review::create([
            'user_id' => $users[0]->id,
            'book_id' => $books[6]->id,
            'rating' => 5,
            'comment' => '保守しやすいコードを書くための考え方が分かりやすくまとめられています。',
        ]);

        Review::create([
            'user_id' => $users[2]->id,
            'book_id' => $books[6]->id,
            'rating' => 4,
            'comment' => 'コードを書くときに意識すべきポイントを改めて学ぶことができました。',
        ]);

        Review::create([
            'user_id' => $users[4]->id,
            'book_id' => $books[6]->id,
            'rating' => 5,
            'comment' => '実際の開発でも役立つ考え方が多く、勉強になりました。',
        ]);

        Review::create([
            'user_id' => $users[1]->id,
            'book_id' => $books[7]->id,
            'rating' => 5,
            'comment' => '自分の生き方について考えるきっかけを与えてくれる本でした。',
        ]);

        Review::create([
            'user_id' => $users[2]->id,
            'book_id' => $books[7]->id,
            'rating' => 4,
            'comment' => '対話形式なので読みやすく、心理学の考え方を理解しやすかったです。',
        ]);

        Review::create([
            'user_id' => $users[4]->id,
            'book_id' => $books[7]->id,
            'rating' => 5,
            'comment' => '悩みを抱えたときに新しい考え方を示してくれる一冊だと思います。',
        ]);

        Review::create([
            'user_id' => $users[0]->id,
            'book_id' => $books[8]->id,
            'rating' => 4,
            'comment' => '芸人同士の関係や葛藤が丁寧に描かれていて印象に残りました。',
        ]);

        Review::create([
            'user_id' => $users[3]->id,
            'book_id' => $books[8]->id,
            'rating' => 5,
            'comment' => '登場人物の感情が伝わってきて、一気に読み進めてしまいました。',
        ]);

        Review::create([
            'user_id' => $users[4]->id,
            'book_id' => $books[8]->id,
            'rating' => 4,
            'comment' => '夢を追う人たちの生き方について考えさせられる作品でした。',
        ]);

        Review::create([
            'user_id' => $users[1]->id,
            'book_id' => $books[9]->id,
            'rating' => 5,
            'comment' => 'データをもとに世界を見ることの大切さを学べました。',
        ]);

        Review::create([
            'user_id' => $users[2]->id,
            'book_id' => $books[9]->id,
            'rating' => 4,
            'comment' => '思い込みで判断していたことに気づくきっかけになりました。',
        ]);

        Review::create([
            'user_id' => $users[3]->id,
            'book_id' => $books[9]->id,
            'rating' => 5,
            'comment' => 'ニュースやデータを見るときの視点が変わる、とても勉強になる本でした。',
        ]);

        Review::create([
            'user_id' => $users[0]->id,
            'book_id' => $books[10]->id,
            'rating' => 4,
            'comment' => '物流の仕組みがどのように変化したのかを知ることができました。',
        ]);

        Review::create([
            'user_id' => $users[4]->id,
            'book_id' => $books[10]->id,
            'rating' => 5,
            'comment' => 'コンテナが世界経済に与えた影響について深く学べる内容でした。',
        ]);
    }
}

