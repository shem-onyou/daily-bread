document.addEventListener('DOMContentLoaded', () => {
    const scrollRoot = document.querySelector('.page-main');

    const targets = [
        '.about-hero-content',
        '.about-hero-image',
        '.about-intro-text',
        '.stat-card',
        '.mission-icon-wrap',
        '.about-mission h2',
        '.about-mission p',
        '.about-values h2',
        '.values-subtitle',
        '.value-card',
        '.about-cta h2',
        '.about-cta p',
        '.about-cta .btn-primary',
        '.footer-section',
    ];

    targets.forEach(selector => {
        document.querySelectorAll(selector).forEach(el => {
            el.classList.add('fade-up');
        });
    });

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, {
        root: scrollRoot,
        threshold: 0.15,
    });

    document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));
});
