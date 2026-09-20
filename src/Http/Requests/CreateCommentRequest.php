<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Http\Requests;

use Fewchurs\Laravel\Validation\Rules;
use Illuminate\Validation\ValidationException;

final class CreateCommentRequest extends BoardRequest
{
    public function authorize(): bool
    {
        return parent::authorize() && $this->boardEnabled('allow_comments');
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'text' => Rules::commentText(),
            'author_name' => Rules::authorName(),
        ];
    }

    public function text(): string
    {
        return trim((string) $this->input('text'));
    }

    public function authorName(): ?string
    {
        $value = trim((string) $this->input('author_name', ''));

        if ($value !== '') {
            return $value;
        }

        $user = $this->user();

        return is_object($user) && isset($user->name) && is_string($user->name) ? $user->name : null;
    }

    protected function failedAuthorization(): void
    {
        throw ValidationException::withMessages([
            'text' => trans('fewchurs::messages.errors.comments_closed'),
        ]);
    }
}
