<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Http\Requests;

use Fewchurs\Laravel\Validation\Rules;
use Illuminate\Validation\ValidationException;

final class SubmitFeatureRequest extends BoardRequest
{
    public function authorize(): bool
    {
        return parent::authorize() && $this->boardEnabled('allow_submissions');
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        $mode = (string) config('fewchurs.board.email_field', 'optional');

        return [
            'title' => Rules::title(),
            'description' => Rules::description(),
            'email' => $mode === 'required' ? Rules::email() : Rules::email(required: false),
        ];
    }

    public function title(): string
    {
        return trim((string) $this->input('title'));
    }

    public function description(): ?string
    {
        $value = trim((string) $this->input('description', ''));

        return $value === '' ? null : $value;
    }

    public function email(): ?string
    {
        if ((string) config('fewchurs.board.email_field', 'optional') === 'hidden') {
            return $this->userEmail();
        }

        $value = trim((string) $this->input('email', ''));

        return $value === '' ? $this->userEmail() : $value;
    }

    protected function failedAuthorization(): void
    {
        throw ValidationException::withMessages([
            'title' => trans('fewchurs::messages.errors.submissions_closed'),
        ]);
    }

    private function userEmail(): ?string
    {
        $user = $this->user();

        return is_object($user) && isset($user->email) && is_string($user->email) ? $user->email : null;
    }
}
