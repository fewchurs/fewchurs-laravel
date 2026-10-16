@once
<script>
(function () {
    // Votes update the button at once and save in the background. Clicks made while a
    // save is in flight are folded in: when it lands, the latest choice is sent if it
    // differs. A failure puts the last saved state back and falls back to a normal post,
    // which shows the server's message on the board.
    function show(form, voted, count) {
        var button = form.querySelector('button');
        var counter = form.querySelector('[data-fewchurs-count]');
        button.setAttribute('aria-pressed', voted ? 'true' : 'false');
        button.setAttribute('aria-label', button.getAttribute(voted ? 'data-label-remove' : 'data-label-vote'));
        if (counter && typeof count === 'number') counter.textContent = String(count);
    }

    function wanted(form) {
        return form.querySelector('button').getAttribute('aria-pressed') === 'true';
    }

    function fallback(form, voted) {
        var method = form.querySelector('input[name="_method"]');
        if (voted) {
            if (method) method.remove();
        } else {
            if (!method) {
                method = document.createElement('input');
                method.type = 'hidden';
                method.name = '_method';
                form.appendChild(method);
            }
            method.value = 'DELETE';
        }
        form.action = form.getAttribute(voted ? 'data-vote-url' : 'data-unvote-url');
        form.submit();
    }

    function save(form) {
        var state = form._fewchurs;
        var voted = wanted(form);
        var body = new FormData(form);
        body.set('_method', voted ? 'POST' : 'DELETE');
        state.busy = true;

        fetch(form.getAttribute(voted ? 'data-vote-url' : 'data-unvote-url'), {
            method: 'POST',
            body: body,
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (response) { return response.ok ? response.json() : Promise.reject(response); })
            .then(function (data) {
                state.busy = false;
                state.voted = !!data.hasVoted;
                if (typeof data.upvoteCount === 'number') state.count = data.upvoteCount;

                if (wanted(form) !== state.voted) {
                    save(form);
                } else {
                    show(form, state.voted, state.count);
                }
            })
            .catch(function () {
                state.busy = false;
                show(form, state.voted, state.count);
                fallback(form, voted);
            });
    }

    document.addEventListener('submit', function (event) {
        var form = event.target.closest('form[data-fewchurs-vote]');
        if (!form) return;

        event.preventDefault();

        var button = form.querySelector('button');
        var counter = form.querySelector('[data-fewchurs-count]');

        if (!form._fewchurs) {
            form._fewchurs = {
                busy: false,
                voted: wanted(form),
                count: counter ? parseInt(counter.textContent, 10) : null
            };
        }

        var next = !wanted(form);
        var shown = counter ? parseInt(counter.textContent, 10) : null;
        show(form, next, shown === null ? null : Math.max(0, shown + (next ? 1 : -1)));

        button.classList.remove('fewchurs__vote--pop');
        void button.offsetWidth;
        if (next) button.classList.add('fewchurs__vote--pop');

        if (!form._fewchurs.busy) save(form);
    });
})();
</script>
@endonce
