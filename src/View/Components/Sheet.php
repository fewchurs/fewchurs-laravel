<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\View\Components;

use Fewchurs\Laravel\Facades\Fewchurs;
use Fewchurs\Laravel\Support\Theme;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Component;

/**
 * The board in a slide-over sheet. The board loads in a same-origin frame of the
 * package's own page, only when the sheet first opens, so it costs nothing on
 * pages where nobody opens it. Without JavaScript the trigger is a plain link.
 */
final class Sheet extends Component
{
    public const SIDES = ['right', 'left', 'bottom'];

    private static int $count = 0;

    public string $id;

    /** Colours come from config('fewchurs.theme'), so the button and the board inside always match. */
    public function __construct(
        public string $side = 'right',
        public ?string $app = null,
        public ?string $tab = null,
        public ?string $label = null,
        public bool $trigger = true,
    ) {
        $this->side = in_array($side, self::SIDES, true) ? $side : 'right';
        $this->id = 'fewchurs-sheet-'.(++self::$count);
    }

    public function shouldRender(): bool
    {
        return Fewchurs::configured($this->app) && Route::has($this->routeName().'index');
    }

    public function render(): View
    {
        $query = array_filter(['board' => $this->app, 'tab' => $this->tab]);
        $mode = config('fewchurs.theme.dark_mode');

        return view('fewchurs::sheet', [
            'pageUrl' => route($this->routeName().'index', $query),
            'embedUrl' => route($this->routeName().'index', $query + ['embed' => 1]),
            'styles' => Theme::styles(),
            'darkMode' => in_array($mode, ['light', 'dark'], true) ? $mode : null,
        ]);
    }

    private function routeName(): string
    {
        return (string) config('fewchurs.route.name', 'fewchurs.');
    }
}
