/**
 * Gallery Lightbox
 * Popup overlay untuk melihat gambar galeri dalam ukuran penuh
 */
(function () {
    'use strict';

    // Store gallery items data
    let galleryItems = [];
    let currentIndex = 0;

    // DOM Elements
    let lightbox = null;
    let lightboxImage = null;
    let lightboxTitle = null;
    let lightboxDesc = null;
    let lightboxCounter = null;
    let prevBtn = null;
    let nextBtn = null;

    /**
     * Initialize the lightbox
     */
    function init() {
        const galleryMediaElements = document.querySelectorAll('.gallery-item-media');

        if (galleryMediaElements.length === 0) return;

        // Collect gallery data
        galleryMediaElements.forEach((media, index) => {
            const img = media.querySelector('img');
            const article = media.closest('.gallery-item');
            const titleEl = article?.querySelector('h3');
            const descEl = article?.querySelector('.text-muted');

            if (img) {
                galleryItems.push({
                    src: img.src,
                    alt: img.alt || '',
                    title: titleEl?.textContent?.trim() || '',
                    description: descEl?.textContent?.trim() || ''
                });

                // Add click handler
                media.addEventListener('click', () => openLightbox(index));
                media.setAttribute('role', 'button');
                media.setAttribute('tabindex', '0');
                media.setAttribute('aria-label', `Lihat gambar: ${titleEl?.textContent?.trim() || 'Galeri'}`);

                // Keyboard support for gallery items
                media.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        openLightbox(index);
                    }
                });
            }
        });

        // Create lightbox DOM
        createLightboxDOM();

        // Event listeners
        setupEventListeners();
    }

    /**
     * Create lightbox DOM elements
     */
    function createLightboxDOM() {
        lightbox = document.createElement('div');
        lightbox.className = 'gallery-lightbox';
        lightbox.setAttribute('role', 'dialog');
        lightbox.setAttribute('aria-modal', 'true');
        lightbox.setAttribute('aria-label', 'Galeri Gambar');

        lightbox.innerHTML = `
      <div class="gallery-lightbox__content">
        <span class="gallery-lightbox__counter" aria-live="polite"></span>
        <button class="gallery-lightbox__close" aria-label="Tutup">
          <i class="bx bx-x"></i>
        </button>
        <button class="gallery-lightbox__nav gallery-lightbox__nav--prev" aria-label="Gambar sebelumnya">
          <i class="bx bx-chevron-left"></i>
        </button>
        <button class="gallery-lightbox__nav gallery-lightbox__nav--next" aria-label="Gambar selanjutnya">
          <i class="bx bx-chevron-right"></i>
        </button>
        <img class="gallery-lightbox__image" src="" alt="" />
        <div class="gallery-lightbox__caption">
          <h4 class="gallery-lightbox__title"></h4>
          <p class="gallery-lightbox__desc"></p>
        </div>
      </div>
    `;

        document.body.appendChild(lightbox);

        // Cache DOM references
        lightboxImage = lightbox.querySelector('.gallery-lightbox__image');
        lightboxTitle = lightbox.querySelector('.gallery-lightbox__title');
        lightboxDesc = lightbox.querySelector('.gallery-lightbox__desc');
        lightboxCounter = lightbox.querySelector('.gallery-lightbox__counter');
        prevBtn = lightbox.querySelector('.gallery-lightbox__nav--prev');
        nextBtn = lightbox.querySelector('.gallery-lightbox__nav--next');
    }

    /**
     * Setup event listeners
     */
    function setupEventListeners() {
        // Close button
        lightbox.querySelector('.gallery-lightbox__close').addEventListener('click', closeLightbox);

        // Navigation buttons
        prevBtn.addEventListener('click', showPrev);
        nextBtn.addEventListener('click', showNext);

        // Click outside to close
        lightbox.addEventListener('click', (e) => {
            if (e.target === lightbox) {
                closeLightbox();
            }
        });

        // Keyboard navigation
        document.addEventListener('keydown', handleKeydown);

        // Swipe support for mobile
        let touchStartX = 0;
        let touchEndX = 0;

        lightbox.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        lightbox.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        }, { passive: true });

        function handleSwipe() {
            const diff = touchStartX - touchEndX;
            const threshold = 50;

            if (Math.abs(diff) > threshold) {
                if (diff > 0) {
                    showNext(); // Swipe left = next
                } else {
                    showPrev(); // Swipe right = prev
                }
            }
        }
    }

    /**
     * Open lightbox at specific index
     */
    function openLightbox(index) {
        currentIndex = index;
        updateLightbox();
        lightbox.classList.add('active');
        document.body.style.overflow = 'hidden';

        // Focus trap
        lightbox.querySelector('.gallery-lightbox__close').focus();
    }

    /**
     * Close lightbox
     */
    function closeLightbox() {
        lightbox.classList.remove('active');
        document.body.style.overflow = '';
    }

    /**
     * Show previous image
     */
    function showPrev() {
        if (currentIndex > 0) {
            currentIndex--;
            updateLightbox();
        }
    }

    /**
     * Show next image
     */
    function showNext() {
        if (currentIndex < galleryItems.length - 1) {
            currentIndex++;
            updateLightbox();
        }
    }

    /**
     * Update lightbox content
     */
    function updateLightbox() {
        const item = galleryItems[currentIndex];

        if (!item) return;

        // Update image with loading state
        lightboxImage.style.opacity = '0.5';
        lightboxImage.src = item.src;
        lightboxImage.alt = item.alt || item.title;

        lightboxImage.onload = () => {
            lightboxImage.style.opacity = '1';
        };

        // Update caption
        lightboxTitle.textContent = item.title;
        lightboxDesc.textContent = item.description;

        // Hide description if empty
        lightboxDesc.style.display = item.description ? 'block' : 'none';

        // Update counter
        lightboxCounter.textContent = `${currentIndex + 1} / ${galleryItems.length}`;

        // Update navigation buttons
        prevBtn.disabled = currentIndex === 0;
        nextBtn.disabled = currentIndex === galleryItems.length - 1;

        // Hide nav if only one image
        if (galleryItems.length <= 1) {
            prevBtn.style.display = 'none';
            nextBtn.style.display = 'none';
            lightboxCounter.style.display = 'none';
        }
    }

    /**
     * Handle keyboard navigation
     */
    function handleKeydown(e) {
        if (!lightbox.classList.contains('active')) return;

        switch (e.key) {
            case 'Escape':
                closeLightbox();
                break;
            case 'ArrowLeft':
                showPrev();
                break;
            case 'ArrowRight':
                showNext();
                break;
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
