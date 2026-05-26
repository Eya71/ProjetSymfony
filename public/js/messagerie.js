(function () {
    const root = document.getElementById('threadContainer');
    if (!root) return;
    const dealId = root.dataset.dealId;
    const list = document.getElementById('messages-list');
    const form = document.getElementById('send-form');
    const input = document.getElementById('message-input');
    let lastId = 0;
    let polling = false;

    function formatTime(dt) {
        try {
            return new Date(String(dt).replace(' ', 'T')).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        } catch (_) {
            return dt;
        }
    }

    function append(msg) {
        const li = document.createElement('li');
        li.className = 'message ' + (msg.mine ? 'message-mine' : 'message-other');
        li.dataset.id = msg.id;

        const bubble = document.createElement('div');
        bubble.className = 'message-bubble';

        const text = document.createElement('div');
        text.className = 'message-text';
        text.textContent = msg.contenu;

        const time = document.createElement('div');
        time.className = 'message-time';
        time.textContent = formatTime(msg.created_at);

        bubble.appendChild(text);
        bubble.appendChild(time);
        li.appendChild(bubble);
        list.appendChild(li);
        if (msg.id > lastId) lastId = msg.id;
    }

    function scrollBottom() {
        list.scrollTop = list.scrollHeight;
    }

    async function poll() {
        if (polling) return;
        polling = true;
        try {
            const r = await fetch('/messagerie/' + dealId + '/messages?since=' + lastId, {
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!r.ok) return;
            const data = await r.json();
            if (data.messages && data.messages.length) {
                data.messages.forEach(append);
                scrollBottom();
            }
        } catch (_) {
        } finally {
            polling = false;
        }
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        const contenu = (input.value || '').trim();
        if (!contenu) return;
        input.value = '';
        try {
            const r = await fetch('/messagerie/' + dealId + '/send', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ contenu: contenu }),
            });
            if (r.ok) {
                const data = await r.json();
                if (data.message) {
                    append(data.message);
                    scrollBottom();
                }
            }
        } catch (_) {
        }
        input.focus();
    });

    poll().then(scrollBottom);
    setInterval(poll, 3000);
})();
