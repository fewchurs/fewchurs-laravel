@once
<script>
(function () {
    document.addEventListener('submit', function (event) {
        var form = event.target.closest('form[data-fewchurs-vote]');
        if (!form) return;

        event.preventDefault();

        var button = form.querySelector('button');
        var pressed = button.getAttribute('aria-pressed') === 'true';
        var body = new FormData(form);
        body.set('_method', pressed ? 'DELETE' : 'POST');
        button.disabled = true;

        fetch(form.action, {
            method: 'POST',
            body: body,
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (response) { return response.ok ? response.json() : Promise.reject(response); })
            .then(function (data) {
                var count = form.querySelector('[data-fewchurs-count]');
                if (count && typeof data.upvoteCount === 'number') count.textContent = String(data.upvoteCount);
                button.setAttribute('aria-pressed', data.hasVoted ? 'true' : 'false');
            })
            .catch(function () { form.submit(); })
            .finally(function () { button.disabled = false; });
    });
})();
</script>
@endonce
