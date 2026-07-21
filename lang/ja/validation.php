<?php

return [

    'accepted' => ':attributeを承認してください。',
    'active_url' => ':attributeには有効なURLを入力してください。',
    'after' => ':attributeには:dateより後の日付を入力してください。',
    'after_or_equal' => ':attributeには:date以降の日付を入力してください。',
    'alpha' => ':attributeには英字のみ入力してください。',
    'alpha_num' => ':attributeには英数字のみ入力してください。',
    'array' => ':attributeは配列で入力してください。',
    'before' => ':attributeには:dateより前の日付を入力してください。',
    'before_or_equal' => ':attributeには:date以前の日付を入力してください。',

    'between' => [
        'array' => ':attributeは:min個から:max個の範囲で入力してください。',
        'file' => ':attributeは:min〜:maxキロバイトで入力してください。',
        'numeric' => ':attributeは:min〜:maxの範囲で入力してください。',
        'string' => ':attributeは:min文字から:max文字で入力してください。',
    ],

    'boolean' => ':attributeにはtrueまたはfalseを指定してください。',
    'confirmed' => ':attributeの確認が一致しません。',
    'current_password' => 'パスワードが正しくありません。',

    'date' => ':attributeには正しい日付を入力してください。',
    'date_format' => ':attributeの形式が正しくありません。',

    'digits' => ':attributeは:digits桁で入力してください。',
    'digits_between' => ':attributeは:min桁から:max桁で入力してください。',

    'email' => ':attributeには有効なメールアドレスを入力してください。',

    'exists' => '選択された:attributeは無効です。',

    'file' => ':attributeはファイルである必要があります。',

    'image' => ':attributeには画像ファイルを指定してください。',

    'in' => '選択された:attributeは無効です。',
    'integer' => ':attributeには整数を入力してください。',

    'max' => [
        'array' => ':attributeは:max個以内で入力してください。',
        'file' => ':attributeは:maxキロバイト以内で入力してください。',
        'numeric' => ':attributeは:max以下で入力してください。',
        'string' => ':attributeは:max文字以内で入力してください。',
    ],

    'min' => [
        'array' => ':attributeは:min個以上で入力してください。',
        'file' => ':attributeは:minキロバイト以上で入力してください。',
        'numeric' => ':attributeは:min以上で入力してください。',
        'string' => ':attributeは:min文字以上で入力してください。',
    ],

    'not_in' => '選択された:attributeは無効です。',

    'numeric' => ':attributeには数字を入力してください。',

    'regex' => ':attributeの形式が正しくありません。',

    'required' => ':attributeは必須です。',
    'required_if' => ':otherが:valueの場合、:attributeは必須です。',
    'required_unless' => ':otherが:value以外の場合、:attributeは必須です。',
    'required_with' => ':valuesが存在する場合、:attributeは必須です。',
    'required_without' => ':valuesが存在しない場合、:attributeは必須です。',

    'same' => ':attributeと:otherが一致しません。',

    'size' => [
        'array' => ':attributeは:size個で入力してください。',
        'file' => ':attributeは:sizeキロバイトである必要があります。',
        'numeric' => ':attributeは:sizeである必要があります。',
        'string' => ':attributeは:size文字で入力してください。',
    ],

    'string' => ':attributeは文字列で入力してください。',

    'unique' => ':attributeは既に登録されています。',

    'uploaded' => ':attributeのアップロードに失敗しました。',

    'url' => ':attributeには有効なURLを入力してください。',

    'uuid' => ':attributeは有効なUUIDである必要があります。',


    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],


    'attributes' => [
        'title' => 'タイトル',
        'author' => '著者',
        'isbn' => 'ISBN-13',
        'publication_date' => '出版日',
        'description' => '説明',
        'image_url' => '画像URL',
        'genre_id' => 'ジャンル',
        'genre_ids' => 'ジャンル',
        'rating' => '評価',
        'comment' => 'コメント',
        'email' => 'メールアドレス',
        'password' => 'パスワード',
        'name' => '名前',
    ],

];
