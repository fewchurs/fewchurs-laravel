<nav class="fewchurs__tabs" aria-label="{{ __('fewchurs::messages.sort') }}">
    @foreach($tabs as $name)
        <a class="fewchurs__tab"
           href="{{ route($routeName.'index', $boardQuery + ['tab' => $name]) }}"
           @if($name === $tab) aria-current="page" @endif>
            {{ __('fewchurs::messages.tabs.'.$name) }}
        </a>
    @endforeach
</nav>
