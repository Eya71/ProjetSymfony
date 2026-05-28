(function () {
    const notifBadge = document.querySelector('[data-notif-badge]');
    const msgBadge = document.querySelector('[data-message-badge]');
    const list = document.getElementById('notificationsList');
    const markAllBtn = document.getElementById('markAllReadBtn');

    function setBadge(el, count) {
        if (!el) return;
        if (count > 0) {
            el.textContent = String(count);
            el.classList.remove('badge-hidden');
            el.style.display = '';
        } else {
            el.textContent = '';
            el.classList.add('badge-hidden');
            el.style.display = 'none';
        }
    }

    async function refreshBadges() {
        try {
            const r = await fetch('/notifications/feed', {
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!r.ok) return null;
            const data = await r.json();
            setBadge(notifBadge, data.unread_count);
            setBadge(msgBadge, data.message_count);
            return data;
        } catch (_) {
            return null;
        }
    }

    if (list) {
        list.addEventListener('click', async function (e) {
            const link = e.target.closest('[data-mark-read]');
            if (!link) return;
            const li = link.closest('.notif-row');
            if (!li) return;
            const id = li.dataset.id;
            if (!id) return;
            try {
                await fetch('/notifications/' + id + '/read', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
            } catch (_) {
            }
            li.classList.remove('is-unread');
            li.classList.add('is-read');
            const dot = li.querySelector('.notif-dot');
            if (dot) dot.remove();
        });
    }

    if (markAllBtn) {
        markAllBtn.addEventListener('click', async function () {
            try {
                await fetch('/notifications/read-all', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
            } catch (_) {
            }
            document.querySelectorAll('.notif-row.is-unread').forEach(li => {
                li.classList.remove('is-unread');
                li.classList.add('is-read');
                const dot = li.querySelector('.notif-dot');
                if (dot) dot.remove();
            });
            setBadge(notifBadge, 0);
        });
    }

    refreshBadges();
    setInterval(refreshBadges, 10000);
})();
