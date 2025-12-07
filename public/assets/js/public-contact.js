/**
 * Contact Form Module
 * Loaded only on contact page
 * Requires: public-core.js (optional)
 */
(() => {
    'use strict';

    const initContactForm = () => {
        const form = document.querySelector('[data-contact-form]');
        if (!form) return;

        const feedback = form.querySelector('[data-contact-feedback]');
        const honeypot = form.querySelector('input[name="website"]');

        const showFeedback = (msg) => {
            if (!feedback) return;
            if (msg) {
                feedback.textContent = msg;
                feedback.classList.add('is-visible');
                feedback.removeAttribute('hidden');
            } else {
                feedback.textContent = '';
                feedback.classList.remove('is-visible');
                feedback.setAttribute('hidden', 'hidden');
            }
        };

        form.addEventListener('submit', (e) => {
            // Bot detection via honeypot
            if (honeypot && String(honeypot.value || '').trim() !== '') {
                e.preventDefault();
                return;
            }

            if (!form.checkValidity()) {
                e.preventDefault();
                showFeedback('Mohon lengkapi bidang wajib sebelum mengirim.');
                form.classList.add('contact-form--has-error');
                form.reportValidity();
            } else {
                showFeedback('');
                form.classList.remove('contact-form--has-error');
            }
        });
    };

    // Initialize
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initContactForm);
    } else {
        initContactForm();
    }
})();
