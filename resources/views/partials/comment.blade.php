{{-- User content from strangers: never switch {{ }} to {!! !!} in this file. --}}
<div class="fewchurs__comment">
    <span class="fewchurs__comment-author">
        {{ $comment->authorName !== '' ? $comment->authorName : __('fewchurs::messages.anonymous') }}
    </span>
    @if($comment->isDeveloper)
        <span class="fewchurs__badge">{{ __('fewchurs::messages.team') }}</span>
    @elseif($comment->isMine)
        <span class="fewchurs__badge">{{ __('fewchurs::messages.yours') }}</span>
    @endif
    <p class="fewchurs__comment-text">{{ $comment->text }}</p>
</div>
