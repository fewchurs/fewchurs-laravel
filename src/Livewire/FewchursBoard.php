<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Livewire;

use Fewchurs\Laravel\Data\CommentPage;
use Fewchurs\Laravel\Exceptions\FewchursException;
use Fewchurs\Laravel\Facades\Fewchurs;
use Fewchurs\Laravel\FewchursApp;
use Fewchurs\Laravel\Support\Ids;
use Fewchurs\Laravel\Support\Theme;
use Fewchurs\Laravel\Validation\Rules;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class FewchursBoard extends Component
{
    public ?string $board = null;

    public string $tab = 'top';

    public ?string $cursor = null;

    public ?string $openFeature = null;

    public string $title = '';

    public string $description = '';

    public string $email = '';

    public string $comment = '';

    public string $authorName = '';

    public function mount(?string $app = null, ?string $tab = null): void
    {
        $this->board = $app;
        $this->tab = $this->tabs()[0] ?? 'top';

        if ($tab !== null && in_array($tab, $this->tabs(), true)) {
            $this->tab = $tab;
        }
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, $this->tabs(), true)) {
            $this->tab = $tab;
            $this->cursor = null;
        }
    }

    public function toggleComments(string $featureId): void
    {
        $this->openFeature = $this->openFeature === $featureId ? null : Ids::assert($featureId);
    }

    public function toggleVote(string $featureId, bool $hasVoted): void
    {
        $this->guardWrites();

        try {
            $hasVoted
                ? $this->app()->features()->unvote($featureId)
                : $this->app()->features()->vote($featureId);
        } catch (FewchursException $e) {
            $this->addError('board', $e->userMessage());
        }
    }

    public function submit(): void
    {
        $this->guardWrites();
        abort_unless(config('fewchurs.board.allow_submissions', true), 403);

        $emailRequired = (string) config('fewchurs.board.email_field', 'optional') === 'required';

        $this->validate([
            'title' => Rules::title(),
            'description' => Rules::description(),
            'email' => Rules::email(required: $emailRequired),
        ]);

        try {
            $this->app()->features()->submit(
                title: trim($this->title),
                description: trim($this->description) ?: null,
                email: trim($this->email) ?: null,
            );
        } catch (FewchursException $e) {
            $this->addError('title', $e->userMessage());

            return;
        }

        $this->reset(['title', 'description']);
        session()->flash('fewchurs.status', trans('fewchurs::messages.submitted'));
    }

    public function postComment(string $featureId): void
    {
        $this->guardWrites();
        abort_unless(config('fewchurs.board.allow_comments', true), 403);

        $this->validate([
            'comment' => Rules::commentText(),
            'authorName' => Rules::authorName(),
        ]);

        try {
            $this->app()->comments()->create($featureId, trim($this->comment), trim($this->authorName) ?: null);
        } catch (FewchursException $e) {
            $this->addError('comment', $e->userMessage());

            return;
        }

        $this->reset('comment');
    }

    public function loadMore(?string $cursor): void
    {
        $this->cursor = is_string($cursor) && Ids::isValid($cursor) ? $cursor : null;
    }

    public function render(): View
    {
        $app = $this->app();

        return view('fewchurs::livewire.board', [
            'page' => $app->features()->list(
                sort: $this->tab === 'new' ? 'new' : 'top',
                limit: (int) config('fewchurs.board.per_page', 20),
                cursor: $this->cursor,
            ),
            'config' => $app->config(),
            'comments' => $this->comments($app),
            'tabs' => $this->tabs(),
            'styles' => Theme::styles(),
            'showCounts' => config('fewchurs.board.show_vote_counts', true) && ! $app->config()->hideVoteCounts,
        ]);
    }

    private function comments(mixed $app): ?CommentPage
    {
        if ($this->openFeature === null || ! config('fewchurs.board.allow_comments', true)) {
            return null;
        }

        try {
            return $app->comments()->list($this->openFeature, 50);
        } catch (FewchursException) {
            return new CommentPage(collect(), null);
        }
    }

    /**
     * @return array<int, string>
     */
    private function tabs(): array
    {
        return array_values((array) config('fewchurs.board.tabs', ['top', 'new']));
    }

    private function app(): FewchursApp
    {
        return Fewchurs::app($this->board);
    }

    private function guardWrites(): void
    {
        abort_if(config('fewchurs.board.require_auth', false) && auth()->guest(), 403);
    }
}
