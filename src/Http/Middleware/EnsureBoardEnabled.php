<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Http\Middleware;

use Closure;
use Fewchurs\Laravel\Facades\Fewchurs;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureBoardEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(Fewchurs::configured(), 404);

        return $next($request);
    }
}
