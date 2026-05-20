document.addEventListener('DOMContentLoaded', () => {
    const scrollRoot = document.querySelector('.page-main');

    const targets = [
        '.gallery-header h1',
        '.gallery-header p',
        '.gallery-item',
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
