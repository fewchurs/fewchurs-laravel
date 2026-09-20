<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class BoardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ! config('fewchurs.board.require_auth', false) || $this->user() !== null;
    }

    protected function boardEnabled(string $key): bool
    {
        return (bool) config('fewchurs.board.'.$key, true);
    }
}
