@extends(config('fewchurs.board.layout') ?: 'fewchurs::layout')

@section(config('fewchurs.board.section', 'content'))
    <x-fewchurs::board
        :app="$board === \Fewchurs\Laravel\Fewchurs::DEFAULT_APP ? null : $board"
        :tab="$tab" />
@endsection
