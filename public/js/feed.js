document.addEventListener('DOMContentLoaded', () => {
    const toast = document.querySelector('[data-feed-toast]');
    const users = window.gradconnMentionUsers || [];
    const menu = document.querySelector('[data-mention-menu]');
    let mentionInput = null;
    let mentionStart = -1;

    const escapeHtml = value => {
        const node = document.createElement('div');
        node.textContent = String(value ?? '');
        return node.innerHTML;
    };
    const initials = name => String(name || 'U').trim().split(/\s+/).slice(0, 2).map(part => part.charAt(0).toUpperCase()).join('') || 'U';
    const notify = message => {
        if (!toast) return;
        toast.textContent = message;
        toast.classList.add('is-visible');
        clearTimeout(toast.timer);
        toast.timer = setTimeout(() => toast.classList.remove('is-visible'), 2400);
    };
    const avatarHtml = comment => comment.profile_photo
        ? `<img src="/uploads/profiles/${encodeURIComponent(comment.profile_photo.split('/').pop())}" alt="">`
        : escapeHtml(initials(comment.fullname));
    const commentHtml = (comment, reply = false) => `<div class="feed-comment${reply ? ' feed-comment--reply' : ''}" data-comment-id="${comment.id}"><div class="feed-avatar feed-avatar--small">${avatarHtml(comment)}</div><div class="feed-comment__content"><div class="feed-comment__bubble"><strong>${escapeHtml(comment.fullname)}</strong><p>${escapeHtml(comment.comment)}</p></div><small>Just now</small></div></div>`;
    const threadHtml = (comment, action, token, listId) => `<div class="feed-comment-thread" data-thread-id="${comment.id}"><div class="feed-comment" data-comment-id="${comment.id}"><div class="feed-avatar feed-avatar--small">${avatarHtml(comment)}</div><div class="feed-comment__content"><div class="feed-comment__bubble"><strong>${escapeHtml(comment.fullname)}</strong><p>${escapeHtml(comment.comment)}</p></div><div class="feed-comment__meta"><small>Just now</small><button type="button" data-reply-toggle="reply-form-${comment.id}" data-reply-name="${escapeHtml(comment.fullname)}">Reply</button></div></div></div><div class="feed-replies" data-replies-for="${comment.id}"></div><form method="POST" action="${escapeHtml(action)}" id="reply-form-${comment.id}" class="feed-comment-form feed-reply-form" data-comments-list="${escapeHtml(listId)}" data-replies-list="${comment.id}" hidden><input type="hidden" name="_token" value="${escapeHtml(token)}"><input type="hidden" name="parent_comment_id" value="${comment.id}"><div class="comment-composer"><input name="comment" maxlength="3000" required autocomplete="off" placeholder="Write a reply…" data-mention-input><button type="submit" aria-label="Post reply"><i class="fas fa-paper-plane"></i></button></div></form></div>`;

    function closeLightbox() {
        const box = document.querySelector('[data-feed-lightbox]');
        if (!box) return;
        box.classList.remove('is-open');
        box.setAttribute('aria-hidden', 'true');
        box.querySelector('img').src = '';
    }

    document.addEventListener('click', event => {
        const replyToggle = event.target.closest('[data-reply-toggle]');
        if (replyToggle) {
            const form = document.getElementById(replyToggle.dataset.replyToggle);
            if (form) {
                form.hidden = !form.hidden;
                if (!form.hidden) {
                    const input = form.querySelector('[name="comment"]');
                    if (!input.value.trim()) input.value = `@${replyToggle.dataset.replyName} `;
                    input.focus();
                }
            }
            return;
        }
        const toggle = event.target.closest('[data-comment-toggle]');
        if (toggle) {
            const section = document.getElementById(toggle.dataset.commentToggle);
            if (section) {
                section.classList.toggle('is-collapsed');
                if (!section.classList.contains('is-collapsed')) document.getElementById(toggle.dataset.commentFocus || '')?.focus();
            }
            return;
        }
        const jobToggle = event.target.closest('[data-job-toggle]');
        if (jobToggle) {
            const list = document.querySelector('[data-job-list]');
            const expanded = list?.classList.toggle('is-expanded');
            jobToggle.textContent = expanded ? 'Show less' : 'Show more';
            return;
        }
        const image = event.target.closest('[data-feed-image]');
        if (image) {
            const box = document.querySelector('[data-feed-lightbox]');
            if (box) {
                box.querySelector('img').src = image.dataset.feedImage;
                box.classList.add('is-open');
                box.setAttribute('aria-hidden', 'false');
            }
            return;
        }
        if (event.target.closest('[data-feed-lightbox] > button') || event.target.matches('[data-feed-lightbox]')) closeLightbox();
    });
    document.addEventListener('keydown', event => { if (event.key === 'Escape') closeLightbox(); });

    document.addEventListener('submit', async event => {
        const form = event.target;
        if (form.matches('.reaction-form')) {
            event.preventDefault();
            const response = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            if (response.ok) {
                const data = await response.json();
                form.closest('.post')?.querySelector('[data-counts]')?.replaceChildren(document.createTextNode(`${data.counts.total} reactions`));
            }
            return;
        }
        if (form.matches('.feed-reaction-form')) {
            event.preventDefault();
            const reaction = event.submitter?.dataset.reaction || event.submitter?.value || 'like';
            const body = new FormData(form);
            body.set('reaction_type', reaction);
            try {
                const response = await fetch(form.action, { method: 'POST', body, headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                if (!response.ok) throw new Error();
                const data = await response.json();
                const post = form.closest('.feed-post');
                const main = form.querySelector('[data-main-reaction]');
                const labels = { like: ['👍', 'Like'], love: ['❤️', 'Love'], haha: ['😂', 'Haha'], angry: ['😡', 'Angry'] };
                main.className = `feed-action reaction-main is-${data.reaction || 'none'}`;
                main.value = data.reaction || 'like';
                main.querySelector('[data-reaction-emoji]').textContent = labels[data.reaction || 'like'][0];
                main.querySelector('[data-reaction-label]').textContent = data.reaction ? labels[data.reaction][1] : 'Like';
                post.querySelector('[data-count-label]').textContent = `${data.counts.total} reaction${data.counts.total === 1 ? '' : 's'}`;
                post.querySelector('.reaction-stack').innerHTML = Object.entries(labels).filter(([key]) => data.counts[key] > 0).map(([, info]) => `<b>${info[0]}</b>`).join('');
            } catch {
                notify('Reaction could not be saved. Please try again.');
            }
            return;
        }
        if (!form.matches('.feed-comment-form')) return;
        event.preventDefault();
        const input = form.querySelector('[name="comment"]');
        if (!input.value.trim()) return;
        const button = form.querySelector('button[type="submit"]');
        button.disabled = true;
        try {
            const response = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            if (!response.ok) throw new Error();
            const data = await response.json();
            const list = document.getElementById(form.dataset.commentsList);
            list.querySelector('[data-empty-comments]')?.remove();
            if (data.comment.parent_comment_id) {
                const replies = list.querySelector(`[data-replies-for="${data.comment.parent_comment_id}"]`);
                replies?.insertAdjacentHTML('beforeend', commentHtml(data.comment, true));
                form.hidden = true;
                notify('Reply posted.');
            } else {
                list.insertAdjacentHTML('beforeend', threadHtml(data.comment, form.action, form.querySelector('[name="_token"]').value, form.dataset.commentsList));
                notify('Comment posted.');
            }
            input.value = '';
            const counter = form.closest('.feed-post').querySelector('.feed-comment-count');
            counter.textContent = `${data.comment_count} comment${data.comment_count === 1 ? '' : 's'}`;
        } catch {
            notify('Comment could not be posted. Please try again.');
        } finally {
            button.disabled = false;
        }
    });

    function hideMentions() { if (menu) menu.hidden = true; }
    function updateMentions(input) {
        if (!menu) return;
        const pos = input.selectionStart || 0;
        const before = input.value.slice(0, pos);
        const at = before.lastIndexOf('@');
        if (at < 0 || (at > 0 && /\S/.test(before.charAt(at - 1)))) return hideMentions();
        const query = before.slice(at + 1);
        if (query.length > 40 || /\n/.test(query)) return hideMentions();
        const matches = users.filter(user => String(user.name).toLowerCase().includes(query.toLowerCase())).slice(0, 8);
        if (!matches.length) return hideMentions();
        mentionInput = input;
        mentionStart = at;
        menu.innerHTML = matches.map(user => `<button type="button" data-mention-name="${escapeHtml(user.name)}"><span>${escapeHtml(initials(user.name))}</span>${escapeHtml(user.name)}</button>`).join('');
        const rect = input.getBoundingClientRect();
        menu.style.left = `${rect.left + scrollX}px`;
        menu.style.top = `${rect.bottom + scrollY + 6}px`;
        menu.style.width = `${Math.max(rect.width, 245)}px`;
        menu.hidden = false;
    }
    document.addEventListener('input', event => { if (event.target.matches('[data-mention-input]')) updateMentions(event.target); });
    document.addEventListener('click', event => {
        const item = event.target.closest('[data-mention-name]');
        if (item && mentionInput) {
            const pos = mentionInput.selectionStart || 0;
            const insertion = `@${item.dataset.mentionName} `;
            mentionInput.value = mentionInput.value.slice(0, mentionStart) + insertion + mentionInput.value.slice(pos);
            mentionInput.focus();
            mentionInput.setSelectionRange(mentionStart + insertion.length, mentionStart + insertion.length);
            hideMentions();
        } else if (!event.target.closest('[data-mention-menu]') && !event.target.matches('[data-mention-input]')) {
            hideMentions();
        }
    });
});
