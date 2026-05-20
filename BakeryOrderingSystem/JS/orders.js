let orders = ORDERS_DATA || [];

const PAGE_SIZE_ORDERS = 3;
let currentPage = 1;

function subtotal(order) {
    return order.items.reduce((s, i) => s + i.price * i.qty, 0);
}
function formatDate(dateStr) {
    const [y, m, d] = dateStr.split('-');
    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    return `${months[parseInt(m,10)-1]} ${parseInt(d,10)}, ${y}`;
}
function formatPeso(amount) {
    return `₱${parseFloat(amount).toFixed(2)}`;
}

let activeFilter = 'all';

function renderOrders() {
    const list     = document.getElementById('ordersList');
    const empty    = document.getElementById('ordersEmpty');
    const filtered = activeFilter === 'all' ? orders : orders.filter(o => o.status === activeFilter);

    list.innerHTML = '';

    if (filtered.length === 0) {
        empty.classList.add('show');
        renderOrdersPagination([], 1);
        return;
    }
    empty.classList.remove('show');

    const totalPages = Math.ceil(filtered.length / PAGE_SIZE_ORDERS) || 1;
    currentPage = Math.min(Math.max(1, currentPage), totalPages);
    const start  = (currentPage - 1) * PAGE_SIZE_ORDERS;
    const paged  = filtered.slice(start, start + PAGE_SIZE_ORDERS);

    paged.forEach(order => {
        const sub   = subtotal(order);
        const total = sub + parseFloat(order.shipping);
        const qty   = order.items.reduce((s, i) => s + parseInt(i.qty), 0);

        const card = document.createElement('div');
        card.className = `order-card ${order.status}`;
        card.innerHTML = `
            <div class="order-card-top">
                <div>
                    <p class="order-id">${order.id}</p>
                    <p class="order-date">${formatDate(order.date)}</p>
                </div>
                <span class="order-status-badge ${order.status}">${order.status}</span>
            </div>
            <div class="order-card-details">
                <div class="order-detail-item">
                    <span class="order-detail-label">Items</span>
                    <span class="order-detail-value">${qty} item${qty !== 1 ? 's' : ''}</span>
                </div>
                <div class="order-detail-item">
                    <span class="order-detail-label">Shipping</span>
                    <span class="order-detail-value">${formatPeso(order.shipping)}</span>
                </div>
                <div class="order-detail-item">
                    <span class="order-detail-label">Total</span>
                    <span class="order-detail-value price">${formatPeso(total)}</span>
                </div>
                <div class="order-detail-item">
                    <span class="order-detail-label">Payment</span>
                    <span class="order-detail-value">${order.payment}</span>
                </div>
            </div>
            <div class="order-card-footer">
                <i class='bx bx-info-circle'></i> Click to view details
            </div>
        `;
        card.addEventListener('click', () => openModal(order.id));
        list.appendChild(card);
    });

    renderOrdersPagination(filtered, currentPage);
}

function renderOrdersPagination(filtered, page) {
    const container = document.getElementById('ordersPagination');
    if (!container) return;
    container.innerHTML = '';

    const total = Math.ceil(filtered.length / PAGE_SIZE_ORDERS) || 1;
    if (total <= 1) return;

    const mkBtn = (label, pg, disabled, active) => {
        const b = document.createElement('button');
        b.className = 'orders-page-btn' + (active ? ' active' : '') + (disabled ? ' disabled' : '');
        b.textContent = label;
        b.disabled = disabled;
        if (!disabled) b.addEventListener('click', () => {
            currentPage = pg;
            renderOrders();
            document.getElementById('ordersList').scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
        return b;
    };

    container.appendChild(mkBtn('←', page - 1, page === 1, false));

    const range = new Set([1, total, page - 1, page, page + 1].filter(p => p >= 1 && p <= total));
    let prev = 0;
    [...range].sort((a, b) => a - b).forEach(p => {
        if (prev && p - prev > 1) {
            const dots = document.createElement('span');
            dots.className = 'orders-page-dots';
            dots.textContent = '…';
            container.appendChild(dots);
        }
        container.appendChild(mkBtn(p, p, false, p === page));
        prev = p;
    });

    container.appendChild(mkBtn('→', page + 1, page === total, false));
}

document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        activeFilter = btn.dataset.filter;
        currentPage = 1;
        renderOrders();
    });
});

const orderModal = document.getElementById('orderModal');
const cancelBtn  = document.getElementById('cancelOrderBtn');
let activeOrderId = null;

function openModal(orderId) {
    const order = orders.find(o => o.id === orderId);
    if (!order) return;
    activeOrderId = orderId;

    const sub   = subtotal(order);
    const total = sub + parseFloat(order.shipping);

    document.getElementById('modalOrderId').textContent   = order.id;
    document.getElementById('modalOrderDate').textContent = formatDate(order.date);

    const statusEl = document.getElementById('modalStatus');
    statusEl.textContent = order.status;
    statusEl.className   = `order-status-badge ${order.status}`;

    document.getElementById('modalItems').innerHTML = order.items.map(item => `
        <div class="modal-item">
            <div>
                <p class="modal-item-name">${item.name}</p>
                <p class="modal-item-qty">x${item.qty} @ ${formatPeso(item.price)} each</p>
            </div>
            <span class="modal-item-price">${formatPeso(item.price * item.qty)}</span>
        </div>
    `).join('');

    document.getElementById('modalSubtotal').textContent = formatPeso(sub);
    document.getElementById('modalShipping').textContent = formatPeso(order.shipping);
    document.getElementById('modalTotal').textContent    = formatPeso(total);
    document.getElementById('modalPayment').textContent  = order.payment;

    cancelBtn.classList.toggle('show', order.status === 'pending');
    orderModal.classList.add('active');
}

function closeModal() {
    orderModal.classList.remove('active');
    activeOrderId = null;
}

document.getElementById('modalClose').addEventListener('click', closeModal);
document.getElementById('closeModalBtn').addEventListener('click', closeModal);
orderModal.addEventListener('click', (e) => { if (e.target === orderModal) closeModal(); });

cancelBtn.addEventListener('click', async () => {
    if (!confirm(`Cancel order ${activeOrderId}? This cannot be undone.`)) return;
    try {
        const res  = await fetch(CANCEL_URL, {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: activeOrderId }),
        });
        const data = await res.json();
        if (data.success) {
            const order = orders.find(o => o.id === activeOrderId);
            if (order) order.status = 'cancelled';
            closeModal();
            currentPage = 1;
            renderOrders();
        } else { alert(data.error || 'Could not cancel order.'); }
    } catch { alert('Network error.'); }
});

renderOrders();
