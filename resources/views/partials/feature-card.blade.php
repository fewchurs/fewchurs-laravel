{{-- User content from strangers: never switch {{ }} to {!! !!} in this file. --}}
@php
    $isOpen = $openFeature === $feature->id;
    $voteRoute = route($routeName.($feature->hasVoted ? 'unvote' : 'vote'), ['feature' => $feature->id]);
    $commentsUrl = route($routeName.'index', $boardQuery + array_filter([
        'tab' => $tab,
        'feature' => $isOpen ? null : $feature->id,
    ])).'#fewchurs-feature-'.$feature->id;
@endphp

<li class="fewchurs__card" id="fewchurs-feature-{{ $feature->id }}">
    <form method="POST" action="{{ $voteRoute }}" data-fewchurs-vote
          data-vote-url="{{ route($routeName.'vote', ['feature' => $feature->id]) }}"
          data-unvote-url="{{ route($routeName.'unvote', ['feature' => $feature->id]) }}">
        @csrf
        @if($feature->hasVoted)
            @method('DELETE')
        @endif
        @include('fewchurs::partials.board-input')
        <button class="fewchurs__vote" type="submit" aria-pressed="{{ $feature->hasVoted ? 'true' : 'false' }}"
                aria-label="{{ $feature->hasVoted ? __('fewchurs::messages.remove_vote') : __('fewchurs::messages.vote') }}"
                data-label-vote="{{ __('fewchurs::messages.vote') }}"
                data-label-remove="{{ __('fewchurs::messages.remove_vote') }}">
            <span class="fewchurs__vote-arrow" aria-hidden="true">&#9650;</span>
            @if($showCounts)
                <span class="fewchurs__vote-count" data-fewchurs-count>{{ $feature->upvoteCount }}</span>
            @endif
        </button>
    </form>

    <div class="fewchurs__body">
        <h3 class="fewchurs__title">{{ $feature->title }}</h3>

        @if($feature->description !== '')
            <p class="fewchurs__desc">{{ $feature->description }}</p>
        @endif

        <div class="fewchurs__meta">
            @if($feature->status !== \Fewchurs\Laravel\Data\FeatureStatus::Open)
                <span class="fewchurs__status fewchurs__status--{{ $feature->status->value }}">
                    {{ $feature->status->label() }}
                </span>
            @endif

            @if(config('fewchurs.board.allow_comments', true))
                <a class="fewchurs__link" href="{{ $commentsUrl }}">
                    {{ trans_choice('fewchurs::messages.comments', $feature->commentCount, ['count' => $feature->commentCount]) }}
                </a>
            @endif

            @if(config('fewchurs.board.allow_follow', true))
                @include('fewchurs::partials.follow-form', ['feature' => $feature])
            @endif

            @if($feature->isMine)
                <span class="fewchurs__badge">{{ __('fewchurs::messages.yours') }}</span>
            @endif
        </div>

        @if($isOpen && $comments !== null)
            @include('fewchurs::partials.comments', ['feature' => $feature])
        @endif
    </div>
</li>
