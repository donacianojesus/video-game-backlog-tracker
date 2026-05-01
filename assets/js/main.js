 (function () {
    'use strict';

    var tabs = document.querySelectorAll('.tab-btn');
    var rows = document.querySelectorAll('#game-table tbody tr');

    if (tabs.length && rows.length) {
        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                tabs.forEach(function (t) { t.classList.remove('tab-btn--active'); });
                tab.classList.add('tab-btn--active');

                var filter = tab.dataset.tab;
                var n      = 1;

                rows.forEach(function (row) {
                    var show = filter === 'all' || row.dataset.status === filter;
                    row.style.display = show ? '' : 'none';
                    if (show) {
                        var numCell = row.querySelector('.col-num');
                        if (numCell) numCell.textContent = n++;
                    }
                });
            });
        });
    }

    var titleInput   = document.getElementById('title');
    var coverInput   = document.getElementById('cover_url');
    var dropdown     = document.getElementById('search-dropdown');
    var preview      = document.getElementById('cover-preview');
    var previewImg   = document.getElementById('cover-img');
    var clearBtn     = document.getElementById('cover-clear');

    if (titleInput && dropdown) {
        var debounceTimer  = null;
        var highlighted    = -1;
        var currentResults = [];

        titleInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            var q = titleInput.value.trim();
            if (q.length < 2) { closeDropdown(); return; }
            debounceTimer = setTimeout(function () { fetchGames(q); }, 350);
        });

        titleInput.addEventListener('keydown', function (e) {
            var items = dropdown.querySelectorAll('.search-result');
            if (!items.length) return;
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                highlighted = Math.min(highlighted + 1, items.length - 1);
                updateHighlight(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                highlighted = Math.max(highlighted - 1, 0);
                updateHighlight(items);
            } else if (e.key === 'Enter' && highlighted >= 0) {
                e.preventDefault();
                selectResult(currentResults[highlighted]);
            } else if (e.key === 'Escape') {
                closeDropdown();
            }
        });

        document.addEventListener('click', function (e) {
            if (!titleInput.contains(e.target) && !dropdown.contains(e.target)) {
                closeDropdown();
            }
        });

        function fetchGames(q) {
            dropdown.innerHTML = '<div class="search-loading">Searching…</div>';
            dropdown.style.display = 'block';
            highlighted = -1;

            fetch('/video-game-backlog-tracker/search_games.php?q=' + encodeURIComponent(q))
                .then(function (r) { return r.json(); })
                .then(function (data) { currentResults = data; renderDropdown(data); })
                .catch(function () {
                    dropdown.innerHTML = '<div class="search-no-results">Search unavailable.</div>';
                });
        }

        function renderDropdown(results) {
            if (!results.length) {
                dropdown.innerHTML = '<div class="search-no-results">No results found.</div>';
                return;
            }
            dropdown.innerHTML = '';
            results.forEach(function (game, idx) {
                var item = document.createElement('div');
                item.className = 'search-result';

                var img = document.createElement('img');
                img.className = 'search-result-thumb';
                img.src = game.cover || '';
                img.alt = '';

                var info = document.createElement('div');
                info.className = 'search-result-info';

                var name = document.createElement('span');
                name.className = 'search-result-name';
                name.textContent = game.name;

                var year = document.createElement('span');
                year.className = 'search-result-year';
                year.textContent = game.year || '';

                info.appendChild(name);
                info.appendChild(year);
                item.appendChild(img);
                item.appendChild(info);

                item.addEventListener('click', function () { selectResult(game); });
                dropdown.appendChild(item);
            });
        }

        function selectResult(game) {
            titleInput.value = game.name;
            if (coverInput) coverInput.value = game.cover || '';
            if (game.cover && preview && previewImg) {
                previewImg.src = game.cover;
                preview.style.display = 'flex';
            }
            closeDropdown();
        }

        function updateHighlight(items) {
            items.forEach(function (el, i) {
                el.classList.toggle('highlighted', i === highlighted);
            });
        }

        function closeDropdown() {
            dropdown.style.display = 'none';
            dropdown.innerHTML = '';
            highlighted = -1;
            currentResults = [];
        }

        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                if (coverInput) coverInput.value = '';
                if (preview)    preview.style.display = 'none';
                if (previewImg) previewImg.src = '';
            });
        }
    }

    var statusSelect = document.getElementById('status');
    var ratingGroup  = document.getElementById('rating-group');

    function toggleRating() {
        if (!statusSelect || !ratingGroup) return;
        ratingGroup.style.display = statusSelect.value === 'completed' ? 'block' : 'none';
    }

    if (statusSelect && ratingGroup) {
        statusSelect.addEventListener('change', toggleRating);
        toggleRating();
    }

    document.querySelectorAll('.delete-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!confirm('Remove this game from your backlog?')) {
                e.preventDefault();
            }
        });
    });

    var gameForm = document.getElementById('game-form');
    if (gameForm) {
        gameForm.addEventListener('submit', function (e) {
            var title  = document.getElementById('title');
            var status = document.getElementById('status');
            var rating = document.getElementById('rating');
            var valid  = true;

            clearErrors();

            if (title && title.value.trim() === '') {
                showError(title, 'Title is required.');
                valid = false;
            }
            if (status && status.value === 'completed' && rating && rating.value === '') {
                showError(rating, 'Please select a rating for completed games.');
                valid = false;
            }
            if (!valid) e.preventDefault();
        });
    }

    function showError(field, message) {
        var hint = document.createElement('span');
        hint.className = 'form-error';
        hint.textContent = message;
        hint.style.cssText = 'color:#e87f75;font-size:0.78rem;display:block;margin-top:4px';
        field.parentNode.appendChild(hint);
        field.style.borderColor = '#c0392b';
    }

    function clearErrors() {
        document.querySelectorAll('.form-error').forEach(function (el) { el.remove(); });
        document.querySelectorAll('input, select, textarea').forEach(function (el) {
            el.style.borderColor = '';
        });
    }

}());
