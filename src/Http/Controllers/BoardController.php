<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Http\Controllers;

use Fewchurs\Laravel\Exceptions\FewchursException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class BoardController
{
    use InteractsWithBoard;

    /**
     * Data loading lives in the Board component so the page and the tag stay identical.
     * `?embed=1` is the bare page the sheet frames; only this site may frame it.
     */
    public function index(Request $request): Response
    {
        $embed = $request->boolean('embed');

        $response = response()->view('fewchurs::page', [
            'board' => $this->board($request)->name,
            'tab' => $this->tab($request),
            'embed' => $embed,
        ]);

        if ($embed) {
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
            $response->headers->set('Content-Security-Policy', "frame-ancestors 'self'");
            $response->headers->set('X-Robots-Tag', 'noindex');
        }

        return $response;
    }

    public function features(Request $request): JsonResponse
    {
        try {
            $page = $this->board($request)->features()->list(
                sort: $this->tab($request) === 'new' ? 'new' : 'top',
                limit: (int) config('fewchurs.board.per_page', 20),
                cursor: $this->cursor($request),
            );
        } catch (FewchursException $e) {
            return $this->jsonError($e);
        }

        return response()->json($page->toArray());
    }

    public function comments(Request $request, string $feature): JsonResponse
    {
        abort_unless(config('fewchurs.board.allow_comments', true), 404);

        try {
            $page = $this->board($request)->comments()->list(
                $feature,
                min(100, max(1, (int) $request->query('limit', 20))),
                $this->cursor($request),
            );
        } catch (FewchursException $e) {
            return $this->jsonError($e);
        }

        return response()->json($page->toArray());
    }

    private function jsonError(FewchursException $e): JsonResponse
    {
        return response()->json(
            ['error' => ['code' => $e->errorCode, 'message' => $e->userMessage()]],
            $e->status >= 400 && $e->status < 600 ? $e->status : 502,
        );
    }
}
