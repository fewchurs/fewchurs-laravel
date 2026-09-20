@if($board !== \Fewchurs\Laravel\Fewchurs::DEFAULT_APP)
    <input type="hidden" name="board" value="{{ $board }}">
@endif
@if($tab)
    <input type="hidden" name="tab" value="{{ $tab }}">
@endif
