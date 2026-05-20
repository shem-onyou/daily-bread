function updateCartBadge() {
    const cart  = JSON.parse(localStorage.getItem('cartItems') || '[]');
    const total = cart.reduce((sum, i) => sum + (parseInt(i.qty) || 0), 0);
    const badge = document.getElementById('cartBadge');
    if (!badge) return;
    badge.textContent   = total > 99 ? '99+' : total;
    badge.style.display = 'flex';
}

document.addEventListener('DOMContentLoaded', updateCartBadge);

window.addEventListener('storage', (e) => {
    if (e.key === 'cartItems') updateCartBadge();
});
