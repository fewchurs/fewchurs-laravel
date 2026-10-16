@extends($embed ? 'fewchurs::embed' : (config('fewchurs.board.layout') ?: 'fewchurs::layout'))

@section($embed ? 'content' : config('fewchurs.board.section', 'content'))
    <x-fewchurs::board
        :app="$board === \Fewchurs\Laravel\Fewchurs::DEFAULT_APP ? null : $board"
        :tab="$tab"
        :embed="$embed" />
@endsection
