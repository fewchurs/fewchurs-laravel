<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Http\Controllers;

use Fewchurs\Laravel\Exceptions\FewchursException;
use Fewchurs\Laravel\Exceptions\ValidationFailedException;
use Fewchurs\Laravel\Facades\Fewchurs;
use Fewchurs\Laravel\FewchursApp;
use Fewchurs\Laravel\Support\Ids;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

trait InteractsWithBoard
{
    protected function board(Request $request): FewchursApp
    {
        $name = $request->input('board');

        if (! is_string($name) || $name === '') {
            return Fewchurs::app();
        }

        abort_unless(array_key_exists($name, (array) config('fewchurs.apps', [])), 404);

        return Fewchurs::app($name);
    }

    protected function tab(Request $request): string
    {
        $tabs = array_values((array) config('fewchurs.board.tabs', ['top', 'new']));
        $tab = (string) $request->query('tab', (string) config('fewchurs.board.default_tab', 'top'));

        return in_array($tab, $tabs, true) ? $tab : (string) ($tabs[0] ?? 'top');
    }

    protected function cursor(Request $request): ?string
    {
        $cursor = $request->query('cursor');

        return is_string($cursor) && Ids::isValid($cursor) ? $cursor : null;
    }

    protected function featureId(Request $request, string $key = 'feature'): ?string
    {
        $id = $request->query($key);

        return is_string($id) && Ids::isValid($id) ? $id : null;
    }

    /**
     * Redirects are built from the package's own route, never from the Referer header.
     *
     * @param  array<string, mixed>  $query
     */
    protected function backToBoard(Request $request, array $query = [], ?string $fragment = null): RedirectResponse
    {
        $params = array_filter([
            'tab' => $request->input('tab'),
            'board' => $request->input('board'),
        ] + $query, fn ($value) => is_string($value) && $value !== '');

        $url = route((string) config('fewchurs.route.name', 'fewchurs.').'index', $params);

        return redirect()->to($fragment !== null ? $url.'#'.$fragment : $url);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $redirectQuery
     */
    protected function respond(
        Request $request,
        array $payload,
        string $message,
        array $redirectQuery = [],
        ?string $fragment = null,
    ): JsonResponse|RedirectResponse {
        if ($request->expectsJson()) {
            return response()->json($payload);
        }

        return $this->backToBoard($request, $redirectQuery, $fragment)
            ->with('fewchurs.status', $message);
    }

    protected function fail(
        Request $request,
        FewchursException $exception,
        string $field = 'title',
    ): JsonResponse|RedirectResponse {
        if ($exception instanceof ValidationFailedException) {
            throw ValidationException::withMessages([$field => $exception->userMessage()]);
        }

        if ($request->expectsJson()) {
            return response()->json(
                ['error' => ['code' => $exception->errorCode, 'message' => $exception->userMessage()]],
                $exception->status >= 400 && $exception->status < 600 ? $exception->status : 502,
            );
        }

        return $this->backToBoard($request)->with('fewchurs.error', $exception->userMessage());
    }
}
