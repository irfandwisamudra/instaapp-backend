<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();
        $userId = $user?->id;

        $usernameRules = [
            'sometimes',
            'string',
            'min:3',
            'max:50',
            'regex:/^[a-zA-Z0-9_]+$/',
        ];

        // Only enforce unique constraint if the username is being changed to a different value
        if ($this->filled('username') && $user && strtolower((string) $this->input('username')) !== strtolower((string) $user->username)) {
            $usernameRules[] = Rule::unique('users', 'username')->ignore($userId);
        }

        return [
            'name'     => ['sometimes', 'string', 'max:255'],
            'username' => $usernameRules,
            'bio'      => ['sometimes', 'nullable', 'string', 'max:500'],
            'avatar'   => [
                'sometimes',
                'nullable',
                'image',
                'mimes:jpeg,png,gif,webp',
                'max:' . config('app.post_image_max_size_kb', 5120),
            ],
            'remove_avatar' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'username.regex'  => 'Username may only contain letters, numbers, and underscores.',
            'username.unique' => 'That username is already taken.',
            'avatar.image'    => 'Avatar must be an image file.',
            'avatar.mimes'    => 'Avatar must be a JPEG, PNG, GIF, or WebP file.',
            'avatar.max'      => 'Avatar file size must not exceed 5 MB.',
        ];
    }
}
