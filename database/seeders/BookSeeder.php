<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Book;
use App\Models\Genre;
use App\Models\User;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();

        $book = Book::firstOrCreate(
            ['isbn' => '9784101010014'],
            [
                'user_id' => $user->id,
                'title' => '吾輩は猫である',
                'author' => '夏目漱石',
                'published_date' => '1905-01-01',
                'description' => '夏目漱石による代表的な長編小説。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=1',
            ]
        );
        $genres = Genre::whereIn('name', ['小説'])->pluck('id');
        $book->genres()->sync($genres);

        $book = Book::firstOrCreate(
            ['isbn' => '9784422100524'],
            [
                'user_id' => $user->id,
                'title' => '人を動かす',
                'author' => 'D・カーネギー',
                'published_date' => '1936-10-01',
                'description' => '人間関係や人との接し方について学べるビジネス書。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=2',
            ]
        );
        $genres = Genre::whereIn('name', ['ビジネス', '自己啓発'])->pluck('id');
        $book->genres()->sync($genres);


        $book = Book::firstOrCreate(
            ['isbn' => '9784873115658'],
            [
                'user_id' => $user->id,
                'title' => 'リーダブルコード',
                'author' => 'Dustin Boswell',
                'published_date' => '2012-06-23',
                'description' => '読みやすく保守しやすいコードを書くための技術書。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=3',
            ]
        );
        $genres = Genre::whereIn('name', ['技術書'])->pluck('id');
        $book->genres()->sync($genres);

        $book = Book::firstOrCreate(
            ['isbn' => '9784863940246'],
            [
                'user_id' => $user->id,
                'title' => '7つの習慣',
                'author' => 'スティーブン・R・コヴィー',
                'published_date' => '2013-08-30',
                'description' => '人生や仕事における成功の原則を学べる自己啓発書。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=4',
            ]
        );
        $genres = Genre::whereIn('name', ['ビジネス', '自己啓発'])->pluck('id');
        $book->genres()->sync($genres);


        $book = Book::firstOrCreate(
            ['isbn' => '9784101010021'],
            [
                'user_id' => $user->id,
                'title' => '坊っちゃん',
                'author' => '夏目漱石',
                'published_date' => '1906-04-01',
                'description' => '正義感の強い主人公を描いた夏目漱石の代表作。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=5',
            ]
        );
        $genres = Genre::whereIn('name', ['小説'])->pluck('id');
        $book->genres()->sync($genres);


        $book = Book::firstOrCreate(
            ['isbn' => '9784309226712'],
            [
                'user_id' => $user->id,
                'title' => 'サピエンス全史',
                'author' => 'ユヴァル・ノア・ハラリ',
                'published_date' => '2016-09-08',
                'description' => '人類の歴史を壮大な視点から描いた歴史書。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=6',
            ]
        );
        $genres = Genre::whereIn('name', ['歴史', '科学'])->pluck('id');
        $book->genres()->sync($genres);

        $book = Book::firstOrCreate(
            ['isbn' => '9784048930598'],
            [
                'user_id' => $user->id,
                'title' => 'Clean Code',
                'author' => 'Robert C. Martin',
                'published_date' => '2017-12-18',
                'description' => '読みやすく品質の高いソースコードを書くための技術書。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=7',
            ]
        );
        $genres = Genre::whereIn('name', ['技術書'])->pluck('id');
        $book->genres()->sync($genres);

        $book = Book::firstOrCreate(
            ['isbn' => '9784478025819'],
            [
                'user_id' => $user->id,
                'title' => '嫌われる勇気',
                'author' => '岸見一郎・古賀史健',
                'published_date' => '2013-12-13',
                'description' => 'アドラー心理学を対話形式でわかりやすく解説した自己啓発書。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=8',
            ]
        );
        $genres = Genre::whereIn('name', ['自己啓発'])->pluck('id');
        $book->genres()->sync($genres);


        $book = Book::firstOrCreate(
            ['isbn' => '9784163902302'],
            [
                'user_id' => $user->id,
                'title' => '火花',
                'author' => '又吉直樹',
                'published_date' => '2015-03-11',
                'description' => 'お笑い芸人を主人公にした芥川賞受賞作品。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=9',
            ]
        );
        $genres = Genre::whereIn('name', ['小説'])->pluck('id');
        $book->genres()->sync($genres);


        $book = Book::firstOrCreate(
            ['isbn' => '9784822289607'],
            [
                'user_id' => $user->id,
                'title' => 'FACTFULNESS',
                'author' => 'ハンス・ロスリング',
                'published_date' => '2019-01-11',
                'description' => 'データをもとに世界を正しく理解するための考え方を紹介する本。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=10',
            ]
        );
        $genres = Genre::whereIn('name', ['ビジネス', '科学'])->pluck('id');
        $book->genres()->sync($genres);


        $book = Book::firstOrCreate(
            ['isbn' => '9784822251468'],
            [
                'user_id' => $user->id,
                'title' => 'コンテナ物語',
                'author' => 'マルク・レビンソン',
                'published_date' => '2007-01-18',
                'description' => 'コンテナが世界の物流や経済をどのように変えたのかを描いた歴史書。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=11',
            ]
        );
        $genres = Genre::whereIn('name', ['ビジネス', '歴史'])->pluck('id');
        $book->genres()->sync($genres);
    }
}
