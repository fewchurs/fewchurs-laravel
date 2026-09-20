<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Http\Controllers;

use Fewchurs\Laravel\Exceptions\FewchursException;
use Fewchurs\Laravel\Exceptions\LimitReachedException;
use Fewchurs\Laravel\Http\Requests\CreateCommentRequest;
use Fewchurs\Laravel\Http\Requests\FollowFeatureRequest;
use Fewchurs\Laravel\Http\Requests\SubmitFeatureRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Every browser-facing write lands here: the API key stays server-side and only
 * whitelisted, device-scoped fields are handed back to the page.
 */
final class ProxyController
{
    use InteractsWithBoard;

    public function submit(SubmitFeatureRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $feature = $this->board($request)->features()->submit(
                title: $request->title(),
                description: $request->description(),
                email: $request->email(),
                isSubscriber: false,
            );
        } catch (LimitReachedException) {
            return $this->respond($request, ['ok' => true], trans('fewchurs::messages.submitted'));
        } catch (FewchursException $e) {
            return $this->fail($request, $e);
        }

        return $this->respond(
            $request,
            ['feature' => $feature->toArray()],
            trans('fewchurs::messages.submitted'),
            fragment: 'fewchurs-feature-'.$feature->id,
        );
    }

    public function vote(Request $request, string $feature): JsonResponse|RedirectResponse
    {
        return $this->toggleVote($request, $feature, vote: true);
    }

    public function unvote(Request $request, string $feature): JsonResponse|RedirectResponse
    {
        return $this->toggleVote($request, $feature, vote: false);
    }

    public function follow(FollowFeatureRequest $request, string $feature): JsonResponse|RedirectResponse
    {
        try {
            $this->board($request)->features()->follow($feature, $request->email());
        } catch (FewchursException $e) {
            return $this->fail($request, $e, 'email');
        }

        return $this->respond(
            $request,
            ['following' => true],
            trans('fewchurs::messages.following'),
            fragment: 'fewchurs-feature-'.$feature,
        );
    }

    public function unfollow(Request $request, string $feature): JsonResponse|RedirectResponse
    {
        try {
            $this->board($request)->features()->unfollow($feature);
        } catch (FewchursException $e) {
            return $this->fail($request, $e, 'email');
        }

        return $this->respond(
            $request,
            ['following' => false],
            trans('fewchurs::messages.unfollowed'),
            fragment: 'fewchurs-feature-'.$feature,
        );
    }

    public function comment(CreateCommentRequest $request, string $feature): JsonResponse|RedirectResponse
    {
        try {
            $comment = $this->board($request)->comments()->create(
                featureId: $feature,
                text: $request->text(),
                authorName: $request->authorName(),
            );
        } catch (FewchursException $e) {
            return $this->fail($request, $e, 'text');
        }

        return $this->respond(
            $request,
            ['comment' => $comment->toArray()],
            trans('fewchurs::messages.commented'),
            ['feature' => $feature],
            'fewchurs-feature-'.$feature,
        );
    }

    private function toggleVote(Request $request, string $feature, bool $vote): JsonResponse|RedirectResponse
    {
        try {
            $features = $this->board($request)->features();
            $result = $vote ? $features->vote($feature) : $features->unvote($feature);
        } catch (FewchursException $e) {
            return $this->fail($request, $e);
        }

        return $this->respond(
            $request,
            $result->toArray(),
            trans('fewchurs::messages.'.($vote ? 'voted' : 'unvoted')),
            fragment: 'fewchurs-feature-'.$feature,
        );
    }
}
