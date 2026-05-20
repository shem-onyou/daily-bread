function getCart() {
    return JSON.parse(localStorage.getItem('cartItems') || '[]');
}
function saveCart(cart) {
    localStorage.setItem('cartItems', JSON.stringify(cart));
}
function showToast(msg) {
    let toast = document.getElementById('cartToast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'cartToast';
        toast.style.cssText = 'position:fixed;bottom:24px;right:24px;background:#4a3728;color:#fff;padding:10px 18px;border-radius:8px;font-size:14px;z-index:9999;opacity:0;transition:opacity 0.3s';
        document.body.appendChild(toast);
    }
    toast.textContent = msg;
    toast.style.opacity = '1';
    clearTimeout(toast._t);
    toast._t = setTimeout(() => { toast.style.opacity = '0'; }, 2000);
}

document.addEventListener('DOMContentLoaded', () => {
    // Attach Add to Cart to each card rendered by PHP
    document.querySelectorAll('.product-card').forEach(card => {
        card.querySelector('.btn-add-cart').addEventListener('click', () => {
            const id    = card.dataset.id;
            const name  = card.dataset.name;
            const price = parseFloat(card.dataset.price);
            const image = card.dataset.image || '';

            const cart     = getCart();
            const existing = cart.find(i => i.id === id);
            if (existing) { existing.qty++; } else { cart.push({ id, name, price, image, qty: 1 }); }
            saveCart(cart);
            showToast(`${name} added to cart!`);
            updateCartBadge();
        });
    });

    // Scroll animations
    const scrollRoot = document.querySelector('.page-main');
    document.querySelectorAll('.product-card').forEach(el => el.classList.add('fade-up'));
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); observer.unobserve(e.target); } });
    }, { root: scrollRoot, threshold: 0.15 });
    document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));
});
