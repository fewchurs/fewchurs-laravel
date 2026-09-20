<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Http\Middleware;

use Closure;
use Fewchurs\Laravel\Support\Honeypot;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

final class VerifyHoneypot
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Honeypot::enabled()) {
            return $next($request);
        }

        $decoy = $request->input(Honeypot::field());
        $token = $request->input(Honeypot::timestampField());

        if (! Honeypot::passes(is_string($decoy) ? $decoy : null, is_string($token) ? $token : null)) {
            throw ValidationException::withMessages([
                'title' => trans('fewchurs::messages.errors.spam'),
            ]);
        }

        return $next($request);
    }
}
