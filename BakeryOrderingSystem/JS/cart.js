// -- Cart state (persisted in localStorage) --
let cartItems = JSON.parse(localStorage.getItem('cartItems') || '[]');

// SHIPPING_FEE and STOCK_CHECK_URL are injected by cart.php
const shippingFee = typeof SHIPPING_FEE !== 'undefined' ? SHIPPING_FEE : 10;

const cartList        = document.getElementById('cartList');
const cartEmpty       = document.getElementById('cartEmpty');
const summarySubtotal = document.getElementById('summarySubtotal');
const summaryTax      = document.getElementById('summaryTax');
const summaryTotal    = document.getElementById('summaryTotal');
const confirmModal    = document.getElementById('confirmModal');
const confirmMessage  = document.getElementById('confirmMessage');
const gcashDetails    = document.getElementById('gcashDetails');

// Live stock map { productId: availableStock }
let stockMap = {};

async function fetchStock() {
    if (cartItems.length === 0) { renderCart(); return; }
    const ids = cartItems.map(i => i.id).join(',');
    try {
        const res  = await fetch(`${STOCK_CHECK_URL}&ids=${encodeURIComponent(ids)}`);
        stockMap   = await res.json();
        // Clamp any cart qty that now exceeds current stock
        let clamped = false;
        cartItems.forEach(item => {
            const avail = stockMap[item.id] ?? Infinity;
            if (item.qty > avail) { item.qty = avail; clamped = true; }
            if (item.qty <= 0)    { item.qty = 0; }
        });
        cartItems = cartItems.filter(i => i.qty > 0);
        if (clamped) saveCart();
        renderCart();
    } catch {
        renderCart();
    }
}

function saveCart() {
    localStorage.setItem('cartItems', JSON.stringify(cartItems));
    updateCartBadge();
}

// -- Render --
function renderCart() {
    cartList.innerHTML = '';

    if (cartItems.length === 0) {
        cartEmpty.classList.add('show');
        cartList.style.display = 'none';
    } else {
        cartEmpty.classList.remove('show');
        cartList.style.display = 'flex';

        cartItems.forEach(item => {
            const avail    = stockMap[item.id] ?? Infinity;
            const atLimit  = item.qty >= avail;
            const stockMsg = (avail !== Infinity && avail <= 10)
                ? `<span class="stock-warning">Only ${avail} left</span>`
                : '';

            const div = document.createElement('div');
            div.className = 'cart-item';
            div.dataset.id = item.id;
            div.innerHTML = `
                <div class="cart-item-img">${item.image ? `<img src="${item.image}" alt="${item.name}">` : '🍞'}</div>
                <div class="cart-item-info">
                    <p class="cart-item-name">${item.name}</p>
                    <p class="cart-item-price">₱${(item.price * item.qty).toFixed(2)}</p>
                    ${stockMsg}
                </div>
                <div class="cart-item-controls">
                    <div class="qty-control">
                        <button class="qty-dec" data-id="${item.id}">−</button>
                        <span>${item.qty}</span>
                        <button class="qty-inc" data-id="${item.id}" ${atLimit ? 'disabled' : ''}>+</button>
                    </div>
                    <button class="btn-remove" data-id="${item.id}" title="Remove">
                        <i class='bx bx-trash'></i>
                    </button>
                </div>
            `;
            cartList.appendChild(div);
        });
    }

    updateSummary();
}

function updateSummary() {
    const payment  = document.querySelector('input[name="payment"]:checked')?.value;
    const fee      = payment === 'pickup' ? 0 : shippingFee;
    const subtotal = cartItems.reduce((sum, i) => sum + i.price * i.qty, 0);
    const total    = subtotal + fee;
    summarySubtotal.textContent = `\u20B1${subtotal.toFixed(2)}`;
    summaryTax.textContent      = fee === 0 ? 'Free' : `\u20B1${fee.toFixed(2)}`;
    summaryTotal.textContent    = `\u20B1${total.toFixed(2)}`;
}

// -- Qty + remove --
cartList.addEventListener('click', (e) => {
    const id = e.target.closest('[data-id]')?.dataset.id;
    if (!id) return;

    if (e.target.closest('.qty-inc')) {
        const item  = cartItems.find(i => i.id === id);
        const avail = stockMap[id] ?? Infinity;
        if (!item) return;
        if (item.qty >= avail) {
            showStockToast(`Only ${avail} item${avail === 1 ? '' : 's'} left in stock`);
            return;
        }
        item.qty++;
        saveCart();
        renderCart();
    }

    if (e.target.closest('.qty-dec')) {
        const item = cartItems.find(i => i.id === id);
        if (item) {
            item.qty--;
            if (item.qty <= 0) cartItems = cartItems.filter(i => i.id !== id);
            saveCart(); renderCart();
        }
    }

    if (e.target.closest('.btn-remove')) {
        cartItems = cartItems.filter(i => i.id !== id);
        saveCart(); renderCart();
    }
});

document.getElementById('clearCartBtn').addEventListener('click', () => {
    if (cartItems.length === 0) return;
    if (confirm('Remove all items from your cart?')) {
        cartItems = [];
        saveCart(); renderCart();
    }
});

// -- Stock toast notification --
function showStockToast(msg) {
    let toast = document.getElementById('stockToast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'stockToast';
        toast.style.cssText = `
            position:fixed;bottom:1.5rem;left:50%;transform:translateX(-50%);
            background:#553423;color:#fff;padding:.65rem 1.4rem;border-radius:8px;
            font-size:.9rem;z-index:9999;opacity:0;transition:opacity .25s;pointer-events:none;
        `;
        document.body.appendChild(toast);
    }
    toast.textContent = msg;
    toast.style.opacity = '1';
    clearTimeout(toast._t);
    toast._t = setTimeout(() => { toast.style.opacity = '0'; }, 2500);
}

// -- Payment toggle --
document.querySelectorAll('input[name="payment"]').forEach(radio => {
    radio.addEventListener('change', () => {
        gcashDetails.classList.toggle('show', radio.value === 'gcash');
        updateSummary();
    });
});

// -- GCash file upload --
const proofUpload    = document.getElementById('proofUpload');
const uploadFilename = document.getElementById('uploadFilename');
const MAX_PROOF_BYTES = 10 * 1024 * 1024; // 10MB
const ALLOWED_TYPES   = ['image/jpeg', 'image/png'];

document.querySelector('.upload-label').addEventListener('click', () => proofUpload.click());
proofUpload.addEventListener('change', () => {
    const file = proofUpload.files[0];
    if (!file) { uploadFilename.textContent = 'No file chosen'; return; }
    if (!ALLOWED_TYPES.includes(file.type)) {
        uploadFilename.textContent = 'Invalid file type. Please upload a JPEG or PNG.';
        proofUpload.value = '';
        return;
    }
    if (file.size > MAX_PROOF_BYTES) {
        uploadFilename.textContent = 'File too large. Maximum size is 10MB.';
        proofUpload.value = '';
        return;
    }
    uploadFilename.textContent = `${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
});

// -- Checkout --
document.getElementById('checkoutBtn').addEventListener('click', async () => {
    if (cartItems.length === 0) { alert('Your cart is empty.'); return; }

    // Re-fetch stock right before placing order
    if (cartItems.length > 0) {
        const ids = cartItems.map(i => i.id).join(',');
        try {
            const res  = await fetch(`${STOCK_CHECK_URL}&ids=${encodeURIComponent(ids)}`);
            stockMap   = await res.json();
        } catch { /* proceed, server will validate */ }
    }

    // Client-side stock check
    for (const item of cartItems) {
        const avail = stockMap[item.id] ?? Infinity;
        if (item.qty > avail) {
            showStockToast(`Only ${avail} item${avail === 1 ? '' : 's'} left for "${item.name}"`);
            renderCart();
            return;
        }
    }

    const payment = document.querySelector('input[name="payment"]:checked').value;

    if (payment === 'gcash' && !proofUpload.files[0]) {
        alert('Please upload your GCash proof of payment before placing the order.');
        return;
    }

    // Validate file again at checkout in case it changed
    const proofFile = proofUpload.files[0];
    if (proofFile) {
        if (!ALLOWED_TYPES.includes(proofFile.type)) {
            alert('Invalid file type. Only JPEG and PNG are accepted.');
            return;
        }
        if (proofFile.size > MAX_PROOF_BYTES) {
            alert('Proof image is too large. Maximum size is 10MB.');
            return;
        }
    }

    // Read file as-is — no canvas re-encoding, preserves full resolution and quality
    let proofBase64 = '';
    if (proofFile) {
        proofBase64 = await new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload  = e => resolve(e.target.result);
            reader.onerror = () => reject(new Error('Failed to read file'));
            reader.readAsDataURL(proofFile);
        });
    }

    const paymentMap = { 'cod': 'Cash on Delivery', 'gcash': 'GCash', 'pickup': 'Pick-up' };

    const payload = {
        items:   cartItems.map(i => ({ id: i.id, name: i.name, qty: i.qty, price: i.price })),
        payment: paymentMap[payment] || 'Cash on Delivery',
        proof:   proofBase64,
    };

    try {
        const res  = await fetch(CHECKOUT_URL, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify(payload),
        });
        const data = await res.json();

        if (data.success) {
            confirmMessage.textContent = `Order ${data.order_id} placed via ${payload.payment}. Thank you!`;
            confirmModal.classList.add('active');
            cartItems = [];
            saveCart(); renderCart();
        } else {
            alert('Error: ' + (data.error || 'Could not place order.'));
        }
    } catch {
        alert('Network error. Please try again.');
    }
});

confirmModal.addEventListener('click', (e) => {
    if (e.target === confirmModal) confirmModal.classList.remove('active');
});

// Initial load: fetch stock then render
fetchStock();
