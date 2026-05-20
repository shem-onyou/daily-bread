// ── Cart helpers ──
function getCart() {
    return JSON.parse(localStorage.getItem('cartItems') || '[]');
}

function saveCart(cart) {
    localStorage.setItem('cartItems', JSON.stringify(cart));
}

function addToCart(product) {
    const cart     = getCart();
    const existing = cart.find(i => i.id === product.id);
    if (existing) { existing.qty++; } else { cart.push({ ...product, qty: 1 }); }
    saveCart(cart);
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

// ── Render featured products ──
function renderFeatured(products) {
    const grid = document.querySelector('.featured-products .products-grid');
    if (!grid) return;
    grid.innerHTML = '';

    products.slice(0, 6).forEach(product => {
        const card = document.createElement('div');
        card.className = 'product-card fade-up';
        card.innerHTML = `
            <div class="product-image">
                <div class="placeholder">
                    ${product.image ? `<img src="${product.image}" alt="${product.name}">` : ''}
                </div>
            </div>
            <div class="product-info">
                <h3>${product.name}</h3>
                <div class="product-price">₱${parseFloat(product.price).toFixed(2)}</div>
                <div class="product-action">
                    <div class="product-qty">
                        <button class="qty-btn">-</button>
                        <input type="number" value="1" min="1" class="qty-input">
                        <button class="qty-btn">+</button>
                    </div>
                    <button class="btn btn-secondary btn-add-cart">Add to Cart</button>
                </div>
            </div>
        `;

        const input = card.querySelector('.qty-input');
        card.querySelectorAll('.qty-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                let val = parseInt(input.value) || 1;
                input.value = btn.textContent.trim() === '+' ? val + 1 : Math.max(1, val - 1);
            });
        });

        card.querySelector('.btn-add-cart').addEventListener('click', () => {
            const qty  = parseInt(input.value) || 1;
            const cart = getCart();
            const existing = cart.find(i => i.id === product.id);
            if (existing) { existing.qty += qty; } else { cart.push({ id: product.id, name: product.name, price: parseFloat(product.price), image: product.image || '', qty }); }
            saveCart(cart);
            showToast(`${product.name} added to cart!`);
        });

        grid.appendChild(card);
    });
}

// ── Contact form ──
function initContactForm() {
    const form = document.querySelector('.message-form');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = {
            name:    form.querySelector('[name="name"]').value.trim(),
            email:   form.querySelector('[name="email"]').value.trim(),
            message: form.querySelector('[name="message"]').value.trim(),
        };

        try {
            const res  = await fetch('/BakeryOrderingSystem/PHP/messages.php', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify(payload),
            });
            const data = await res.json();
            if (data.success) { showToast('Message sent!'); form.reset(); }
            else { alert(data.error || 'Could not send message.'); }
        } catch {
            alert('Network error. Please try again.');
        }
    });
}

// ── Scroll animations ──
function initAnimations() {
    const scrollRoot = document.querySelector('.page-main');
    const targets = [
        '.hero-content', '.hero-image', '.best-seller h2', '.best-seller-card',
        '.about-us h2', '.about-us p', '.about-us button',
        '.featured-products h2', '.product-card', '.message-content', '.footer-section',
    ];
    targets.forEach(sel => document.querySelectorAll(sel).forEach(el => el.classList.add('fade-up')));

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) { entry.target.classList.add('visible'); observer.unobserve(entry.target); }
        });
    }, { root: scrollRoot, threshold: 0.15 });

    document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));
}

// ── Init ──
document.addEventListener('DOMContentLoaded', () => {
    // Attach Add to Cart to each card rendered by PHP
    document.querySelectorAll('.featured-products .product-card').forEach(card => {
        card.querySelector('.btn-add-cart')?.addEventListener('click', () => {
            const id    = card.dataset.id;
            const name  = card.dataset.name;
            const price = parseFloat(card.dataset.price);
            const image = card.dataset.image || '';
            const input = card.querySelector('.qty-input');
            const qty   = parseInt(input?.value) || 1;

            const cart     = getCart();
            const existing = cart.find(i => i.id === id);
            if (existing) { existing.qty += qty; } else { cart.push({ id, name, price, image, qty }); }
            saveCart(cart);
            showToast(`${name} added to cart!`);
            updateCartBadge();
        });

        card.querySelectorAll('.qty-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const input = card.querySelector('.qty-input');
                let val = parseInt(input.value) || 1;
                input.value = btn.textContent.trim() === '+' ? val + 1 : Math.max(1, val - 1);
            });
        });
    });

    initContactForm();
    initAnimations();
});
