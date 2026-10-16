{{-- User content from strangers: never switch {{ }} to {!! !!} in this file. --}}
@php
    $theme = in_array(config('fewchurs.theme.dark_mode'), ['light', 'dark'], true)
        ? config('fewchurs.theme.dark_mode')
        : null;
    $rules = \Fewchurs\Laravel\Validation\Rules::class;
@endphp

<div class="fewchurs" @if($theme) data-theme="{{ $theme }}" @endif style="{{ $styles }}">
    @include('fewchurs::partials.styles')
    @include('fewchurs::partials.alerts')

    @error('board')
        <p class="fewchurs__alert fewchurs__alert--error" role="alert">{{ $message }}</p>
    @enderror

    @if($error !== null)
        @include('fewchurs::partials.load-error')
    @else
        @if(config('fewchurs.board.allow_submissions', true))
            <form class="fewchurs__form" wire:submit="submit">
                <div class="fewchurs__field">
                    <label class="fewchurs__label" for="fewchurs-lw-title">{{ __('fewchurs::messages.form.title') }}</label>
                    <input class="fewchurs__input" id="fewchurs-lw-title" type="text" wire:model="title"
                           maxlength="{{ $rules::TITLE_MAX }}"
                           placeholder="{{ __('fewchurs::messages.form.title_placeholder') }}">
                    @error('title')<span class="fewchurs__error">{{ $message }}</span>@enderror
                </div>

                <div class="fewchurs__field">
                    <label class="fewchurs__label" for="fewchurs-lw-desc">{{ __('fewchurs::messages.form.description') }}</label>
                    <textarea class="fewchurs__textarea" id="fewchurs-lw-desc" wire:model="description"
                              maxlength="{{ $rules::DESCRIPTION_MAX }}"></textarea>
                    @error('description')<span class="fewchurs__error">{{ $message }}</span>@enderror
                </div>

                @if(config('fewchurs.board.email_field', 'optional') !== 'hidden')
                    <div class="fewchurs__field">
                        <label class="fewchurs__label" for="fewchurs-lw-email">{{ __('fewchurs::messages.form.email') }}</label>
                        <input class="fewchurs__input" id="fewchurs-lw-email" type="email" wire:model="email"
                               maxlength="{{ $rules::EMAIL_MAX }}" autocomplete="email">
                        @error('email')<span class="fewchurs__error">{{ $message }}</span>@enderror
                    </div>
                @endif

                <div>
                    <button class="fewchurs__btn" type="submit" wire:loading.attr="disabled">
                        {{ __('fewchurs::messages.form.submit') }}
                    </button>
                </div>
            </form>
        @endif

        @if(count($tabs) > 1)
            <nav class="fewchurs__tabs" aria-label="{{ __('fewchurs::messages.sort') }}">
                @foreach($tabs as $name)
                    <button class="fewchurs__tab" type="button" wire:click="setTab('{{ $name }}')"
                            @if($name === $tab) aria-current="page" @endif
                            style="background: none; border: 0; cursor: pointer; font: inherit;">
                        {{ __('fewchurs::messages.tabs.'.$name) }}
                    </button>
                @endforeach
            </nav>
        @endif

        <div wire:loading.remove wire:target="setTab, loadMore">
            @if($page->isStale)
                <p class="fewchurs__stale">{{ __('fewchurs::messages.stale') }}</p>
            @endif

            @if($page->isEmpty())
                <p class="fewchurs__empty">{{ __('fewchurs::messages.empty') }}</p>
            @else
                <ul class="fewchurs__list">
                    @foreach($page->features as $feature)
                        <li class="fewchurs__card" wire:key="fewchurs-{{ $feature->id }}">
                            <button class="fewchurs__vote" type="button"
                                    wire:click="toggleVote('{{ $feature->id }}', {{ $feature->hasVoted ? 'true' : 'false' }})"
                                    aria-pressed="{{ $feature->hasVoted ? 'true' : 'false' }}"
                                    aria-label="{{ $feature->hasVoted ? __('fewchurs::messages.remove_vote') : __('fewchurs::messages.vote') }}">
                                <span class="fewchurs__vote-arrow" aria-hidden="true">&#9650;</span>
                                @if($showCounts)
                                    <span class="fewchurs__vote-count">{{ $feature->upvoteCount }}</span>
                                @endif
                            </button>

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
                                        <button class="fewchurs__link" type="button"
                                                wire:click="toggleComments('{{ $feature->id }}')"
                                                style="background: none; border: 0; padding: 0; font: inherit; cursor: pointer;">
                                            {{ trans_choice('fewchurs::messages.comments', $feature->commentCount, ['count' => $feature->commentCount]) }}
                                        </button>
                                    @endif

                                    @if($feature->isMine)
                                        <span class="fewchurs__badge">{{ __('fewchurs::messages.yours') }}</span>
                                    @endif
                                </div>

                                @if($openFeature === $feature->id && $comments !== null)
                                    <div class="fewchurs__comments">
                                        @forelse($comments->comments as $comment)
                                            @include('fewchurs::partials.comment', ['comment' => $comment])
                                        @empty
                                            <p class="fewchurs__desc">{{ __('fewchurs::messages.no_comments') }}</p>
                                        @endforelse

                                        @if(config('fewchurs.board.allow_comments', true))
                                            <form class="fewchurs__form" style="margin: 0;"
                                                  wire:submit="postComment('{{ $feature->id }}')">
                                                <div class="fewchurs__field">
                                                    <label class="fewchurs__label" for="fewchurs-lw-comment">
                                                        {{ __('fewchurs::messages.form.comment') }}
                                                    </label>
                                                    <textarea class="fewchurs__textarea" id="fewchurs-lw-comment"
                                                              wire:model="comment" maxlength="{{ $rules::COMMENT_MAX }}"></textarea>
                                                    @error('comment')<span class="fewchurs__error">{{ $message }}</span>@enderror
                                                </div>
                                                @guest
                                                    <div class="fewchurs__field">
                                                        <label class="fewchurs__label" for="fewchurs-lw-author">
                                                            {{ __('fewchurs::messages.form.name') }}
                                                        </label>
                                                        <input class="fewchurs__input" id="fewchurs-lw-author" type="text"
                                                               wire:model="authorName" maxlength="{{ $rules::AUTHOR_NAME_MAX }}">
                                                        @error('authorName')<span class="fewchurs__error">{{ $message }}</span>@enderror
                                                    </div>
                                                @endguest
                                                <div>
                                                    <button class="fewchurs__btn" type="submit" wire:loading.attr="disabled">
                                                        {{ __('fewchurs::messages.form.post_comment') }}
                                                    </button>
                                                </div>
                                            </form>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>

                @if($page->nextCursor)
                    <div class="fewchurs__pagination">
                        <button class="fewchurs__btn fewchurs__btn--ghost" type="button"
                                wire:click="loadMore('{{ $page->nextCursor }}')">
                            {{ __('fewchurs::messages.load_more') }}
                        </button>
                    </div>
                @endif
            @endif
        </div>

        <div wire:loading.block wire:target="setTab, loadMore">
            @include('fewchurs::partials.skeleton', ['form' => false])
        </div>
    @endif

    @if($config->showBranding)
        @include('fewchurs::partials.branding')
    @endif
</div>
