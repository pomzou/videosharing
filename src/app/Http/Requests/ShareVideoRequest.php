<?php

namespace App\Http\Requests;

use App\Models\VideoShare;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShareVideoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255',
                // メールアドレスの形式チェック
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',

                // 禁止ドメインのチェック
                function ($attribute, $value, $fail) {
                    $blockedDomains = [
                        'example.com',
                        'tempmail.com',
                        'disposable.com',
                        'temp-mail.org',
                        'throwawaymail.com',
                        'mailinator.com',
                        'guerrillamail.com',
                        'yopmail.com',
                        'sharklasers.com',
                        '10minutemail.com'
                    ];

                    $domain = substr(strrchr($value, "@"), 1);
                    if (in_array(strtolower($domain), $blockedDomains)) {
                        $fail('This email domain is not allowed. Please use a valid business or personal email address.');
                    }

                    // 一般的な無効なドメインパターンをチェック
                    if (preg_match('/\.(test|invalid|localhost)$/', strtolower($domain))) {
                        $fail('Invalid email domain. Please use a valid email address.');
                    }
                },
                // 既存の有効な共有設定がないかチェック
                Rule::unique('video_shares', 'email')->where(function ($query) {
                    return $query->where('video_file_id', $this->route('videoFile')->id)
                        ->where('is_active', true)
                        ->where('expires_at', '>', now());
                })
            ],
            'expires_at' => [
                'required',
                'date',
                'after:now',
                'before:' . now()->addDays(30)->toDateTimeString(), // 最大30日
            ],
            'confirmation_token' => [
                'sometimes',
                'string'
            ],
            'confirmed' => 'sometimes|boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'This email already has active access to this file.',
            'email.email' => 'Please enter a valid email address.',
            'expires_at.before' => 'The expiration date cannot be more than 30 days in the future.',
            'confirmation_token.required_without' => 'Please confirm the email address.',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('confirmed')) {
            $this->merge([
                'confirmed' => $this->boolean('confirmed')
            ]);
        }
    }

    public function generateConfirmationToken(): string
    {
        return hash('sha256', $this->input('email') . $this->route('videoFile')->id);
    }
}
