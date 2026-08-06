<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'isbn' => ['required', 'digits:13', 'unique:books,isbn'],
            'published_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image_url' => ['nullable', 'string', 'url'],
            'genres' => ['required', 'array', 'min:1'],
            'genres.*' => ['exists:genres,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'タイトルは必須です。',
            'title.max' => 'タイトルは255文字以内で入力してください。',

            'author.required' => '著者は必須です。',
            'author.max' => '著者は255文字以内で入力してください。',

            'isbn.required' => 'ISBN-13は必須です。',
            'isbn.digits' => 'ISBN-13は13桁の数字で入力してください。',
            'isbn.unique' => 'ISBN-13は既に登録されています。',

            'published_date.required' => '出版日は必須です。',
            'published_date.date' => '出版日は有効な日付を入力してください。',

            'description.max' => '説明は1000文字以内で入力してください。',

            'image_url.url' => '画像URLはURL形式で入力してください。',

            'genres.required' => 'ジャンルを1つ以上選択してください。',
            'genres.min' => 'ジャンルを1つ以上選択してください。',
            'genres.*.exists' => '存在しないジャンルが選択されています。',
        ];
    }
}

// genre_idに変更(webのほうも)