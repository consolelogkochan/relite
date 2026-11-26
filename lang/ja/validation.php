<?php

// ReLite で使う規則の分だけを日本語にしている（足りない規則は、fallback_locale の英語になる）
return [
    'boolean' => ':attributeは、はいかいいえで選んでください。',
    'confirmed' => ':attributeが確認の欄と一致しません。',
    'exists' => 'この:attributeは使えません。',
    'max' => [
        'string' => ':attributeは:max文字以内で入れてください。',
    ],
    'min' => [
        'string' => ':attributeは:min文字以上で入れてください。',
    ],
    'regex' => ':attributeの形が正しくありません。',
    'required' => ':attributeを入れてください。',
    'string' => ':attributeは文字で入れてください。',
    'unique' => 'この:attributeはもう使われています。',

    'custom' => [
        'login_id' => [
            'regex' => 'ログインIDは、半角の英小文字・数字・_・-で入れてください。',
            'unique' => 'このログインIDはもう使われています。',
        ],
        'invitation_code' => [
            'exists' => 'この招待コードは使えません。',
        ],
    ],

    'attributes' => [
        'name' => '表示の名前',
        'login_id' => 'ログインID',
        'password' => 'パスワード',
        'invitation_code' => '招待コード',
        'remember' => 'ログインしたままにする',
    ],
];
