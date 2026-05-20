// ── Sidebar toggle ──
const sidebar   = document.getElementById('sidebar');
const overlay   = document.getElementById('overlay');
const toggleBtn = document.getElementById('toggleBtn');

toggleBtn.addEventListener('click', () => { sidebar.classList.toggle('open'); overlay.classList.toggle('active'); });
overlay.addEventListener('click',   () => { sidebar.classList.remove('open'); overlay.classList.remove('active'); });

let orders = ORDERS_DATA ? [...ORDERS_DATA] : [];

// ── Proof modal ──
const proofModal      = document.getElementById('proofModal');
const proofModalImg   = document.getElementById('proofModalImg');
const proofModalClose = document.getElementById('proofModalClose');

proofModalClose.addEventListener('click', () => proofModal.classList.remove('active'));
proofModal.addEventListener('click', (e) => { if (e.target === proofModal) proofModal.classList.remove('active'); });

function openProofModal(src) {
    proofModalImg.src = src;
    proofModal.classList.add('active');
}

// ── Receipt generation ──
function generateReceipt(order) {
    const W = 640, PAD = 40;
    const items       = order.items || [];
    const subtotal    = items.reduce((s, i) => s + i.price * i.qty, 0);
    const shipping    = parseFloat(order.shipping) || 0;
    const total       = subtotal + shipping;
    const lineHeight  = 26;
    const itemRows    = items.length || 1;
    const H           = 420 + itemRows * lineHeight;

    const canvas  = document.createElement('canvas');
    canvas.width  = W * 2;   // 2x for high DPI
    canvas.height = H * 2;
    canvas.style.width  = W + 'px';
    canvas.style.height = H + 'px';
    const ctx = canvas.getContext('2d');
    ctx.scale(2, 2);          // render at 2x, download at full resolution

    // Background
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, W, H);

    // Header band
    ctx.fillStyle = '#553423';
    ctx.fillRect(0, 0, W, 80);

    // Brand name
    ctx.fillStyle = '#ffffff';
    ctx.font = 'bold 22px Segoe UI, sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText('The DailyBread', W / 2, 32);
    ctx.font = '13px Segoe UI, sans-serif';
    ctx.fillStyle = 'rgba(255,255,255,0.75)';
    ctx.fillText('Official Order Receipt', W / 2, 56);

    // Order ID badge
    ctx.fillStyle = '#f0e8e3';
    ctx.beginPath();
    ctx.roundRect(PAD, 96, W - PAD * 2, 36, 8);
    ctx.fill();
    ctx.fillStyle = '#553423';
    ctx.font = 'bold 13px Segoe UI, sans-serif';
    ctx.textAlign = 'left';
    ctx.fillText('Order #', PAD + 12, 119);
    ctx.font = 'bold 13px Courier New, monospace';
    ctx.fillText(order.id, PAD + 68, 119);
    ctx.font = '12px Segoe UI, sans-serif';
    ctx.fillStyle = '#888';
    ctx.textAlign = 'right';
    ctx.fillText(order.date, W - PAD - 12, 119);

    // Customer & payment info
    let y = 158;
    const drawRow = (label, value, bold = false) => {
        ctx.fillStyle = '#999';
        ctx.font = '11px Segoe UI, sans-serif';
        ctx.textAlign = 'left';
        ctx.fillText(label.toUpperCase(), PAD, y);
        ctx.fillStyle = '#2C2C2C';
        ctx.font = (bold ? 'bold ' : '') + '13px Segoe UI, sans-serif';
        ctx.fillText(value, PAD + 130, y);
        y += 22;
    };

    drawRow('Customer', order.customer, true);
    drawRow('Payment', order.payment);
    drawRow('Status', order.status.charAt(0).toUpperCase() + order.status.slice(1));

    // Divider
    y += 6;
    ctx.strokeStyle = '#f0e8e3';
    ctx.lineWidth = 1;
    ctx.beginPath(); ctx.moveTo(PAD, y); ctx.lineTo(W - PAD, y); ctx.stroke();
    y += 16;

    // Items header
    ctx.fillStyle = '#553423';
    ctx.font = 'bold 11px Segoe UI, sans-serif';
    ctx.textAlign = 'left';  ctx.fillText('ITEM', PAD, y);
    ctx.textAlign = 'center'; ctx.fillText('QTY', W / 2, y);
    ctx.textAlign = 'right';  ctx.fillText('AMOUNT', W - PAD, y);
    y += 6;
    ctx.strokeStyle = '#e0d4cc'; ctx.lineWidth = 1;
    ctx.beginPath(); ctx.moveTo(PAD, y); ctx.lineTo(W - PAD, y); ctx.stroke();
    y += 16;

    // Item rows
    if (items.length === 0) {
        ctx.fillStyle = '#aaa'; ctx.font = '12px Segoe UI, sans-serif';
        ctx.textAlign = 'center'; ctx.fillText('No items', W / 2, y); y += lineHeight;
    } else {
        items.forEach(item => {
            ctx.fillStyle = '#2C2C2C'; ctx.font = '12px Segoe UI, sans-serif';
            ctx.textAlign = 'left';  ctx.fillText(item.name, PAD, y);
            ctx.textAlign = 'center'; ctx.fillText(`x${item.qty}`, W / 2, y);
            ctx.textAlign = 'right';  ctx.fillText(`\u20B1${(item.price * item.qty).toFixed(2)}`, W - PAD, y);
            y += lineHeight;
        });
    }

    // Totals
    y += 4;
    ctx.strokeStyle = '#e0d4cc'; ctx.lineWidth = 1;
    ctx.beginPath(); ctx.moveTo(PAD, y); ctx.lineTo(W - PAD, y); ctx.stroke();
    y += 18;

    const drawTotal = (label, value, bold = false) => {
        ctx.fillStyle = bold ? '#553423' : '#555';
        ctx.font = (bold ? 'bold ' : '') + '13px Segoe UI, sans-serif';
        ctx.textAlign = 'left';  ctx.fillText(label, PAD, y);
        ctx.textAlign = 'right'; ctx.fillText(value, W - PAD, y);
        y += 22;
    };

    drawTotal('Subtotal', `\u20B1${subtotal.toFixed(2)}`);
    drawTotal('Shipping Fee', shipping === 0 ? 'Free' : `\u20B1${shipping.toFixed(2)}`);
    y += 2;
    ctx.strokeStyle = '#553423'; ctx.lineWidth = 1.5;
    ctx.beginPath(); ctx.moveTo(PAD, y); ctx.lineTo(W - PAD, y); ctx.stroke();
    y += 14;
    drawTotal('TOTAL', `\u20B1${total.toFixed(2)}`, true);

    // Footer
    y += 10;
    ctx.fillStyle = '#ccc';
    ctx.font = '11px Segoe UI, sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText('Thank you for your order! \u2014 The DailyBread', W / 2, y);
    ctx.fillText('Gov. Drive, San Juan, Dasmarinas Cavite \u00B7 dailybread2k25@gmail.com', W / 2, y + 16);

    // Download
    const link = document.createElement('a');
    link.download = `Receipt-${order.id}.png`;
    link.href = canvas.toDataURL('image/png');
    link.click();
}


async function handleStatusChange(orderId, newStatus, selectEl) {
    try {
        const res  = await fetch(ORDERS_URL, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ order_id: orderId, status: newStatus }),
        });
        const data = await res.json();

        if (data.success) {
            const order = orders.find(o => o.id === orderId);
            if (order) order.status = newStatus;
            selectEl.className = `status-select ${newStatus}`;
        } else {
            alert(data.error || 'Could not update status.');
            selectEl.value = orders.find(o => o.id === orderId)?.status || 'pending';
        }
    } catch {
        alert('Network error.');
    }
}

function getFiltered(filterVal) {
    return filterVal === 'all' ? orders : orders.filter(o => o.status === filterVal);
}

let currentPage = 1;

function renderOrders(filterVal = 'all') {
    const filtered = getFiltered(filterVal);
    const { items, current, total } = paginate(filtered, currentPage);
    currentPage = current;
    const tbody = document.getElementById('ordersTableBody');
    const offset = (current - 1) * PAGE_SIZE;
    tbody.innerHTML = '';

    items.forEach((order, index) => {
        const firstItem  = order.items?.[0];
        const totalQty   = order.items?.reduce((s, i) => s + parseInt(i.qty), 0) || 0;
        const totalPrice = order.items?.reduce((s, i) => s + i.price * i.qty, 0) || 0;

        const tr = document.createElement('tr');

        const tdNum      = document.createElement('td'); tdNum.textContent = offset + index + 1;
        const tdProduct  = document.createElement('td'); tdProduct.textContent  = firstItem ? firstItem.name + (order.items.length > 1 ? ` +${order.items.length - 1} more` : '') : '—';
        const tdCustomer = document.createElement('td'); tdCustomer.textContent = order.customer;
        const tdPayment  = document.createElement('td'); tdPayment.textContent  = order.payment;
        const tdDate     = document.createElement('td'); tdDate.textContent     = order.date;
        const tdQty      = document.createElement('td'); tdQty.textContent      = totalQty;
        const tdPrice    = document.createElement('td'); tdPrice.textContent    = `₱${totalPrice.toFixed(2)}`;

        const tdStatus = document.createElement('td');
        const select   = document.createElement('select');
        select.className = `status-select ${order.status}`;
        ['pending', 'completed', 'cancelled'].forEach(s => {
            const opt = document.createElement('option');
            opt.value = s; opt.textContent = s.charAt(0).toUpperCase() + s.slice(1);
            opt.selected = order.status === s;
            select.appendChild(opt);
        });
        select.addEventListener('change', () => handleStatusChange(order.id, select.value, select));
        tdStatus.appendChild(select);

        const tdProof = document.createElement('td');
        if (order.payment === 'GCash' && order.proof_image) {
            const btn = document.createElement('button');
            btn.className = 'btn-view-proof';
            btn.innerHTML = "<i class='bx bx-show'></i> View";
            btn.addEventListener('click', () => {
                if (order.proof_image.startsWith('data:image')) {
                    openProofModal(order.proof_image);
                } else {
                    window.open(order.proof_image, '_blank');
                }
            });
            tdProof.appendChild(btn);
        } else {
            const na = document.createElement('span'); na.className = 'proof-na'; na.textContent = 'N/A';
            tdProof.appendChild(na);
        }

        const tdReceipt = document.createElement('td');
        const dlBtn = document.createElement('button');
        dlBtn.className = 'btn-download-receipt';
        dlBtn.innerHTML = "<i class='bx bx-download'></i> Download";
        dlBtn.addEventListener('click', () => generateReceipt(order));
        tdReceipt.appendChild(dlBtn);

        tr.append(tdNum, tdProduct, tdCustomer, tdPayment, tdDate, tdQty, tdPrice, tdStatus, tdProof, tdReceipt);
        tbody.appendChild(tr);
    });

    renderPagination('ordersPagination', current, total, (page) => {
        currentPage = page;
        renderOrders(document.getElementById('statusFilter').value);
    });
}

document.getElementById('statusFilter').addEventListener('change', (e) => {
    currentPage = 1;
    renderOrders(e.target.value);
});

// ── Load orders ──
async function loadOrders() {
    try {
        const res = await fetch(ORDERS_URL);
        orders    = await res.json();
        renderOrders();
    } catch {
        document.getElementById('ordersTableBody').innerHTML = '<tr><td colspan="10">Could not load orders.</td></tr>';
    }
}

// ── Logout ──
const logoutModal     = document.getElementById('logoutModal');
const logoutBtn       = document.getElementById('logoutBtn');
const logoutCancelBtn = document.getElementById('logoutCancelBtn');

logoutBtn.addEventListener('click',       () => logoutModal.classList.add('active'));
logoutCancelBtn.addEventListener('click', () => logoutModal.classList.remove('active'));
logoutModal.addEventListener('click', (e) => { if (e.target === logoutModal) logoutModal.classList.remove('active'); });

renderOrders();
