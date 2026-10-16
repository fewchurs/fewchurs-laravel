@if($board !== \Fewchurs\Laravel\Fewchurs::DEFAULT_APP)
    <input type="hidden" name="board" value="{{ $board }}">
@endif
@if($embed ?? false)
    <input type="hidden" name="embed" value="1">
@endif
@if($tab)
    <input type="hidden" name="tab" value="{{ $tab }}">
@endif
