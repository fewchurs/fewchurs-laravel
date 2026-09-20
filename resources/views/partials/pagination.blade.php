<div class="fewchurs__pagination">
    <a class="fewchurs__btn fewchurs__btn--ghost"
       href="{{ route($routeName.'index', $boardQuery + ['tab' => $tab, 'cursor' => $page->nextCursor]) }}">
        {{ __('fewchurs::messages.load_more') }}
    </a>
</div>
