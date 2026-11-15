import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Scroll reveals are opt-in: without JS, or with reduced motion, content stays visible.
if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches && 'IntersectionObserver' in window) {
    document.documentElement.classList.add('has-reveal');

    const observer = new IntersectionObserver(
        (entries) => {
            for (const entry of entries) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            }
        },
        { rootMargin: '0px 0px -10% 0px', threshold: 0.05 },
    );

    const observe = () => {
        document.querySelectorAll('[data-reveal]:not(.is-visible)').forEach((el) => observer.observe(el));
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', observe);
    } else {
        observe();
    }
}

Alpine.start();