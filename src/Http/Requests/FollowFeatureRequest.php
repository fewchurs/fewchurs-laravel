<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Http\Requests;

use Fewchurs\Laravel\Validation\Rules;
use Illuminate\Validation\ValidationException;

final class FollowFeatureRequest extends BoardRequest
{
    public function authorize(): bool
    {
        return parent::authorize() && $this->boardEnabled('allow_follow');
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return ['email' => Rules::email()];
    }

    public function email(): string
    {
        return trim((string) $this->input('email'));
    }

    protected function failedAuthorization(): void
    {
        throw ValidationException::withMessages([
            'email' => trans('fewchurs::messages.errors.generic'),
        ]);
    }
}
