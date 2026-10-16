<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\View\Components;

use Fewchurs\Laravel\Data\CommentPage;
use Fewchurs\Laravel\Exceptions\FewchursException;
use Fewchurs\Laravel\Facades\Fewchurs;
use Fewchurs\Laravel\Support\Ids;
use Fewchurs\Laravel\Support\Theme;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class Board extends Component
{
    /**
     * @param  array<string, mixed>|null  $theme
     */
    public function __construct(
        public ?string $app = null,
        public ?string $tab = null,
        public ?int $limit = null,
        public ?array $theme = null,
        public bool $embed = false,
    ) {}

    public function render(): View
    {
        $board = Fewchurs::app($this->app);
        $tabs = array_values((array) config('fewchurs.board.tabs', ['top', 'new']));
        $tab = $this->resolveTab($tabs);
        $cursor = request()->query('cursor');
        $openFeature = request()->query('feature');

        $page = $board->features()->list(
            sort: $tab === 'new' ? 'new' : 'top',
            limit: $this->limit ?? (int) config('fewchurs.board.per_page', 20),
            cursor: is_string($cursor) && Ids::isValid($cursor) ? $cursor : null,
        );

        return view('fewchurs::board', [
            'board' => $board->name,
            'page' => $page,
            'tab' => $tab,
            'tabs' => $tabs,
            'config' => $board->config(),
            'openFeature' => is_string($openFeature) && Ids::isValid($openFeature) ? $openFeature : null,
            'comments' => $this->comments($board, $openFeature),
            'styles' => Theme::styles($this->theme),
            'routeName' => (string) config('fewchurs.route.name', 'fewchurs.'),
            'embed' => $this->embed,
        ]);
    }

    /**
     * @param  array<int, string>  $tabs
     */
    private function resolveTab(array $tabs): string
    {
        $tab = $this->tab ?? request()->query('tab') ?? config('fewchurs.board.default_tab', 'top');

        return is_string($tab) && in_array($tab, $tabs, true) ? $tab : (string) ($tabs[0] ?? 'top');
    }

    private function comments(mixed $board, mixed $openFeature): ?CommentPage
    {
        if (! is_string($openFeature) || ! Ids::isValid($openFeature) || ! config('fewchurs.board.allow_comments', true)) {
            return null;
        }

        try {
            return $board->comments()->list($openFeature, 50);
        } catch (FewchursException) {
            return new CommentPage(collect(), null);
        }
    }
}
