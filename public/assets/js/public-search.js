/**
 * Search Overlay Module
 * Loaded on pages with search functionality
 * Requires: public-core.js
 */
(() => {
    'use strict';

    const initSearchOverlay = () => {
        const trigger = document.querySelector('[data-search-trigger]');
        const overlay = document.querySelector('[data-search-overlay]');
        if (!trigger || !overlay) return;

        const form = overlay.querySelector('[data-search-form]');
        const input = overlay.querySelector('[data-search-input]');
        const resultsContainer = overlay.querySelector('[data-search-results]');
        const emptyState = overlay.querySelector('[data-search-empty]');
        const closeButtons = overlay.querySelectorAll('[data-search-close]');
        const endpoint = form ? form.getAttribute('data-search-url') : null;

        if (!input || !resultsContainer || !endpoint) return;

        let current = '';
        let controller = null;
        const debounce = window.PublicUtils?.debounce || ((fn, d) => {
            let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), d); };
        });

        const openOverlay = () => {
            overlay.removeAttribute('hidden');
            requestAnimationFrame(() => {
                overlay.classList.add('is-open');
                input.focus();
                document.body.style.overflow = 'hidden';
            });
        };

        const closeOverlay = () => {
            overlay.classList.remove('is-open');
            document.body.style.overflow = '';
            setTimeout(() => {
                overlay.setAttribute('hidden', '');
                input.value = '';
                current = '';
                resultsContainer.innerHTML = '';
                if (emptyState) {
                    resultsContainer.appendChild(emptyState);
                }
            }, 300);
        };

        const allowUrl = (u) => {
            try {
                const url = new URL(u, window.location.origin);
                return url.protocol === 'http:' || url.protocol === 'https:' ? url.toString() : '#';
            } catch (_e) {
                return '#';
            }
        };

        const renderResults = (items, q) => {
            resultsContainer.innerHTML = '';

            if (!items.length) {
                const noResults = document.createElement('div');
                noResults.className = 'search-overlay__no-results';
                noResults.innerHTML = `<i class="bx bx-search-alt"></i><p>Tidak ada hasil untuk "<strong>${q}</strong>"</p>`;
                resultsContainer.appendChild(noResults);
                return;
            }

            const groups = {};
            const groupLabels = {
                berita: { label: 'Berita', icon: 'bx-news' },
                layanan: { label: 'Layanan', icon: 'bx-briefcase' },
                dokumen: { label: 'Dokumen', icon: 'bx-file' }
            };

            items.forEach(item => {
                const type = item.type || 'other';
                if (!groups[type]) groups[type] = [];
                groups[type].push(item);
            });

            Object.entries(groups).forEach(([type, groupItems]) => {
                const groupEl = document.createElement('div');
                groupEl.className = 'search-overlay__group';

                const groupInfo = groupLabels[type] || { label: type, icon: 'bx-folder' };

                const titleEl = document.createElement('div');
                titleEl.className = 'search-overlay__group-title';
                titleEl.innerHTML = `<i class="bx ${groupInfo.icon}"></i> ${groupInfo.label}`;
                groupEl.appendChild(titleEl);

                groupItems.forEach(item => {
                    const a = document.createElement('a');
                    a.className = 'search-overlay__item';
                    a.href = allowUrl(item.url);
                    a.addEventListener('click', () => closeOverlay());

                    const iconWrap = document.createElement('div');
                    iconWrap.className = 'search-overlay__item-icon';
                    iconWrap.innerHTML = `<i class="bx ${item.icon || groupInfo.icon}"></i>`;
                    a.appendChild(iconWrap);

                    const content = document.createElement('div');
                    content.className = 'search-overlay__item-content';

                    const title = document.createElement('div');
                    title.className = 'search-overlay__item-title';
                    title.textContent = item.title || 'Tanpa judul';
                    content.appendChild(title);

                    if (item.snippet) {
                        const snippet = document.createElement('div');
                        snippet.className = 'search-overlay__item-snippet';
                        snippet.textContent = item.snippet;
                        content.appendChild(snippet);
                    }

                    a.appendChild(content);
                    groupEl.appendChild(a);
                });

                resultsContainer.appendChild(groupEl);
            });
        };

        const fetchResults = async (q) => {
            if (controller) { try { controller.abort(); } catch (_) { } }
            try { controller = new AbortController(); } catch (_) { controller = null; }

            resultsContainer.innerHTML = '<div class="search-overlay__loading"><i class="bx bx-loader-alt bx-spin"></i> Mencari...</div>';

            try {
                let url;
                try { url = new URL(endpoint, window.location.origin); }
                catch (_) { url = new URL(window.location.origin + endpoint.replace(/^\//, '')); }
                url.searchParams.set('q', q);
                url.searchParams.set('limit', '12');

                const res = await fetch(url.toString(), {
                    headers: { Accept: 'application/json' },
                    signal: controller ? controller.signal : undefined
                });

                if (!res.ok) throw new Error(`Status ${res.status}`);
                const payload = await res.json();
                if (current !== q) return;
                renderResults(Array.isArray(payload.results) ? payload.results : [], q);
            } catch (e) {
                if (controller && e && e.name === 'AbortError') return;
                if (current === q) {
                    resultsContainer.innerHTML = '<div class="search-overlay__no-results"><i class="bx bx-error-circle"></i><p>Terjadi kesalahan saat mencari.</p></div>';
                }
            } finally {
                controller = null;
            }
        };

        const debouncedSearch = debounce(fetchResults, 350);

        // Event listeners
        trigger.addEventListener('click', openOverlay);

        closeButtons.forEach(btn => {
            btn.addEventListener('click', closeOverlay);
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && overlay.classList.contains('is-open')) {
                closeOverlay();
            }
        });

        input.addEventListener('input', (e) => {
            const v = String(e.target.value || '').trim();
            current = v;

            if (v.length < 2) {
                debouncedSearch.cancel?.();
                if (controller) { try { controller.abort(); } catch (_) { } controller = null; }
                resultsContainer.innerHTML = '';
                if (emptyState) resultsContainer.appendChild(emptyState);
                return;
            }

            debouncedSearch(v);
        });

        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                const q = input.value.trim();
                if (q) {
                    closeOverlay();
                    window.location.href = form.action + '?q=' + encodeURIComponent(q);
                }
            });
        }
    };

    // Initialize
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSearchOverlay);
    } else {
        initSearchOverlay();
    }
})();
