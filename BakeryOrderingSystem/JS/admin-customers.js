// ── Sidebar toggle ──
const sidebar   = document.getElementById('sidebar');
const overlay   = document.getElementById('overlay');
const toggleBtn = document.getElementById('toggleBtn');

toggleBtn.addEventListener('click', () => { sidebar.classList.toggle('open'); overlay.classList.toggle('active'); });
overlay.addEventListener('click',   () => { sidebar.classList.remove('open'); overlay.classList.remove('active'); });

// ── Message modal ──
const modalOverlay      = document.getElementById('modalOverlay');
const modalClose        = document.getElementById('modalClose');
const modalCustomerName = document.getElementById('modalCustomerName');
const modalMessage      = document.getElementById('modalMessage');

modalClose.addEventListener('click', () => modalOverlay.classList.remove('active'));
modalOverlay.addEventListener('click', (e) => { if (e.target === modalOverlay) modalOverlay.classList.remove('active'); });

// ── Pagination state ──
const allRows = [...document.querySelectorAll('#customersTableBody tr')];
let currentPage = 1;

function renderCustomersPage(page) {
    const { items, current, total } = paginate(allRows, page);
    currentPage = current;
    allRows.forEach(r => r.style.display = 'none');
    items.forEach(r => r.style.display = '');

    // Re-attach message button handlers for visible rows
    items.forEach(r => {
        const btn = r.querySelector('.btn-message');
        if (btn) {
            btn.onclick = () => {
                modalCustomerName.textContent = `Message from ${btn.dataset.name}`;
                modalMessage.textContent      = btn.dataset.message || 'No message.';
                modalOverlay.classList.add('active');
            };
        }
    });

    renderPagination('customersPagination', current, total, renderCustomersPage);
}

renderCustomersPage(1);

// ── Logout ──
const logoutModal     = document.getElementById('logoutModal');
const logoutBtn       = document.getElementById('logoutBtn');
const logoutCancelBtn = document.getElementById('logoutCancelBtn');

logoutBtn.addEventListener('click',       () => logoutModal.classList.add('active'));
logoutCancelBtn.addEventListener('click', () => logoutModal.classList.remove('active'));
logoutModal.addEventListener('click', (e) => { if (e.target === logoutModal) logoutModal.classList.remove('active'); });
