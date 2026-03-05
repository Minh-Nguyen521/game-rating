// ============================================================
// GameVault – Main JavaScript
// ============================================================

document.addEventListener('DOMContentLoaded', () => {

    // ── Mobile nav toggle ──────────────────────────────────
    const toggle = document.getElementById('navToggle');
    const nav    = document.getElementById('mainNav');

    if (toggle && nav) {
        toggle.addEventListener('click', () => {
            nav.classList.toggle('open');
            toggle.setAttribute('aria-expanded', nav.classList.contains('open'));
        });

        // Close nav when a link is clicked
        nav.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => nav.classList.remove('open'));
        });
    }

    // ── File upload preview ────────────────────────────────
    const fileInput   = document.getElementById('cover_image');
    const previewWrap = document.getElementById('imagePreview');

    if (fileInput && previewWrap) {
        fileInput.addEventListener('change', () => {
            const file = fileInput.files[0];
            if (!file) return;

            if (!file.type.startsWith('image/')) {
                showFieldError('cover_image', 'Please select a valid image file.');
                fileInput.value = '';
                return;
            }
            if (file.size > 2 * 1024 * 1024) {
                showFieldError('cover_image', 'Image must be smaller than 2 MB.');
                fileInput.value = '';
                return;
            }

            clearFieldError('cover_image');
            const reader = new FileReader();
            reader.onload = e => {
                previewWrap.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
            };
            reader.readAsDataURL(file);
        });
    }

    // ── Client-side form validation ────────────────────────
    const gameForm = document.getElementById('gameForm');
    if (gameForm) {
        gameForm.addEventListener('submit', e => {
            let valid = true;

            // Title
            const title = document.getElementById('title');
            if (title && title.value.trim().length < 2) {
                showFieldError('title', 'Title must be at least 2 characters.');
                valid = false;
            } else { clearFieldError('title'); }

            // Genre
            const genre = document.getElementById('genre_id');
            if (genre && !genre.value) {
                showFieldError('genre_id', 'Please select a genre.');
                valid = false;
            } else { clearFieldError('genre_id'); }

            // Year
            const year = document.getElementById('release_year');
            if (year) {
                const y = parseInt(year.value);
                if (isNaN(y) || y < 1970 || y > new Date().getFullYear() + 2) {
                    showFieldError('release_year', 'Enter a valid release year (1970 – present).');
                    valid = false;
                } else { clearFieldError('release_year'); }
            }

            // Description
            const desc = document.getElementById('description');
            if (desc && desc.value.trim().length < 10) {
                showFieldError('description', 'Description must be at least 10 characters.');
                valid = false;
            } else { clearFieldError('description'); }

            if (!valid) e.preventDefault();
        });
    }

    // ── Review form validation ─────────────────────────────
    const reviewForm = document.getElementById('reviewForm');
    if (reviewForm) {
        reviewForm.addEventListener('submit', e => {
            let valid = true;

            const name = document.getElementById('reviewer_name');
            if (name && name.value.trim().length < 2) {
                showFieldError('reviewer_name', 'Name must be at least 2 characters.');
                valid = false;
            } else { clearFieldError('reviewer_name'); }

            const score = document.getElementById('score');
            if (score) {
                const s = parseInt(score.value);
                if (isNaN(s) || s < 1 || s > 10) {
                    showFieldError('score', 'Score must be between 1 and 10.');
                    valid = false;
                } else { clearFieldError('score'); }
            }

            const review = document.getElementById('review_text');
            if (review && review.value.trim().length < 5) {
                showFieldError('review_text', 'Review text must be at least 5 characters.');
                valid = false;
            } else { clearFieldError('review_text'); }

            if (!valid) e.preventDefault();
        });
    }

    // ── Interactive star rating picker ─────────────────────
    const scoreInput = document.getElementById('score');
    const starPicker = document.getElementById('starPicker');

    if (scoreInput && starPicker) {
        renderStarPicker(parseInt(scoreInput.value) || 0);

        starPicker.addEventListener('mouseover', e => {
            const star = e.target.closest('[data-val]');
            if (star) highlightStars(parseInt(star.dataset.val));
        });

        starPicker.addEventListener('mouseout', () => {
            highlightStars(parseInt(scoreInput.value) || 0);
        });

        starPicker.addEventListener('click', e => {
            const star = e.target.closest('[data-val]');
            if (star) {
                const val = parseInt(star.dataset.val);
                scoreInput.value = val;
                highlightStars(val);
            }
        });
    }

    function renderStarPicker(active) {
        if (!starPicker) return;
        starPicker.innerHTML = '';
        for (let i = 1; i <= 10; i++) {
            const s = document.createElement('span');
            s.dataset.val = i;
            s.textContent = i <= active ? '★' : '☆';
            s.style.cssText = `cursor:pointer;font-size:1.5rem;color:${i <= active ? 'var(--gold)' : 'var(--border)'};transition:color .15s;`;
            starPicker.appendChild(s);
        }
    }

    function highlightStars(val) {
        if (!starPicker) return;
        starPicker.querySelectorAll('[data-val]').forEach(s => {
            const v = parseInt(s.dataset.val);
            s.textContent = v <= val ? '★' : '☆';
            s.style.color  = v <= val ? 'var(--gold)' : 'var(--border)';
        });
    }

    // ── Delete confirmation ────────────────────────────────
    document.querySelectorAll('.confirm-delete').forEach(btn => {
        btn.addEventListener('click', e => {
            if (!confirm('Are you sure you want to delete this game? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });

    // ── Auto-dismiss alerts ────────────────────────────────
    document.querySelectorAll('.alert[data-auto-dismiss]').forEach(el => {
        setTimeout(() => el.remove(), 4000);
    });

    // ── Helpers ────────────────────────────────────────────
    function showFieldError(fieldId, msg) {
        const field = document.getElementById(fieldId);
        if (!field) return;
        field.classList.add('input-error');
        let err = field.parentElement.querySelector('.error-msg');
        if (!err) {
            err = document.createElement('span');
            err.className = 'error-msg';
            field.parentElement.appendChild(err);
        }
        err.textContent = msg;
    }

    function clearFieldError(fieldId) {
        const field = document.getElementById(fieldId);
        if (!field) return;
        field.classList.remove('input-error');
        const err = field.parentElement.querySelector('.error-msg');
        if (err) err.remove();
    }
});
