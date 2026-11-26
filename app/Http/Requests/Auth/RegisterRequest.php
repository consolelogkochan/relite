<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * ログインID は、確かめる前に小文字にそろえる（表示の名前の前後の空白は TrimStrings が取る）
     */
    protected function prepareForValidation(): void
    {
        $loginId = $this->input('login_id');

        if (is_string($loginId)) {
            $this->merge(['login_id' => Str::lower($loginId)]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50'],
            'login_id' => ['required', 'string', 'min:3', 'max:30', 'regex:/\A[a-z0-9_-]+\z/', 'unique:users,login_id'],
            'password' => ['required', 'confirmed', Password::defaults()],
            // 招待コードは二重に守る。ここで「存在し、まだ使われていない」を見て、ほかの欄の誤りと一緒に返す。
            // 同時に同じコードで登録したときは、RegisterController がトランザクションの中で鍵を掛けて読み直し、最後の関所になる。
            // 誤りの文は、存在しないときも使用済みのときも同じ（使えるコードを探す手がかりにしないため）
            'invitation_code' => ['required', 'string', Rule::exists('invitation_codes', 'code')->whereNull('used_at')],
        ];
    }
}
