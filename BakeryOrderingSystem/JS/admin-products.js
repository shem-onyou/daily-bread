// ── Sidebar toggle ──
const sidebar   = document.getElementById('sidebar');
const overlay   = document.getElementById('overlay');
const toggleBtn = document.getElementById('toggleBtn');

toggleBtn.addEventListener('click', () => { sidebar.classList.toggle('open'); overlay.classList.toggle('active'); });
overlay.addEventListener('click',   () => { sidebar.classList.remove('open'); overlay.classList.remove('active'); });

// ── State ──
let products = PRODUCTS_DATA ? [...PRODUCTS_DATA] : [];
// Attach images from separate map so PRODUCTS_DATA stays small
products.forEach(p => { p.image = PRODUCTS_IMAGES[p.id] || ''; });

function getNextId() {
    if (products.length === 0) return 'PRD-001';
    const nums = products
        .map(p => parseInt(p.id.replace('PRD-', ''), 10))
        .filter(n => !isNaN(n));
    const max = nums.length > 0 ? Math.max(...nums) : 0;
    return `PRD-${String(max + 1).padStart(3, '0')}`;
}

// ── Table helpers ──
function makeStockBadge(stock) {
    const span  = document.createElement('span');
    const level = stock <= 10 ? 'low' : stock <= 30 ? 'medium' : 'high';
    span.className   = `stock-badge ${level}`;
    span.textContent = stock;
    return span;
}

function buildRow(product, rowNum) {
    const tr = document.createElement('tr');
    tr.dataset.id = product.id;

    const tdNum = document.createElement('td');
    tdNum.className   = 'row-num';
    tdNum.textContent = rowNum;

    const tdImg = document.createElement('td');
    if (product.image) {
        const img = document.createElement('img');
        img.src = product.image; img.alt = product.name; img.className = 'product-thumb';
        tdImg.appendChild(img);
    }

    const tdId   = document.createElement('td');
    const idSpan = document.createElement('span');
    idSpan.className = 'product-id'; idSpan.textContent = product.id;
    tdId.appendChild(idSpan);

    const tdName  = document.createElement('td');
    tdName.textContent = product.name;

    const tdStock = document.createElement('td');
    tdStock.appendChild(makeStockBadge(product.stock));

    const tdPrice = document.createElement('td');
    tdPrice.textContent = `₱${parseFloat(product.price).toFixed(2)}`;

    const tdAction  = document.createElement('td');
    tdAction.className = 'action-cell';

    const btnEdit = document.createElement('button');
    btnEdit.className = 'btn-edit';
    btnEdit.innerHTML = "<i class='bx bxs-edit'></i> Edit";
    btnEdit.addEventListener('click', () => openEditModal(product.id));

    const btnDelete = document.createElement('button');
    btnDelete.className = 'btn-delete';
    btnDelete.innerHTML = "<i class='bx bxs-trash'></i> Delete";
    btnDelete.addEventListener('click', () => handleDelete(product.id));

    tdAction.append(btnEdit, btnDelete);
    tr.append(tdNum, tdImg, tdId, tdName, tdStock, tdPrice, tdAction);
    return tr;
}

let currentPage = 1;

function renderProducts() {
    const { items, current, total } = paginate(products, currentPage);
    currentPage = current;
    const tbody = document.getElementById('productsTableBody');
    tbody.innerHTML = '';
    const offset = (current - 1) * PAGE_SIZE;
    items.forEach((p, i) => tbody.appendChild(buildRow(p, offset + i + 1)));
    renderPagination('productsPagination', current, total, (page) => {
        currentPage = page;
        renderProducts();
    });
}

// ── Modal elements ──
const addProductModal  = document.getElementById('addProductModal');
const addProductError  = document.getElementById('addProductError');
const imagePreview     = document.getElementById('imagePreview');
const inputProductFile = document.getElementById('inputProductFile');
const inputImageUrl    = document.getElementById('inputProductImageUrl');
const inputProductId   = document.getElementById('inputProductId');
const inputName        = document.getElementById('inputProductName');
const inputStock       = document.getElementById('inputProductStock');
const inputPrice       = document.getElementById('inputProductPrice');

let uploadedImageData = null;
let editingId         = null;

function setPreview(src) {
    imagePreview.innerHTML = src
        ? `<img src="${src}" alt="Preview">`
        : `<i class='bx bx-image-alt'></i>`;
}

inputProductFile.addEventListener('change', () => {
    const file = inputProductFile.files[0];
    if (!file || !file.type.startsWith('image/')) return;
    const reader = new FileReader();
    reader.onload = (e) => { uploadedImageData = e.target.result; inputImageUrl.value = ''; setPreview(uploadedImageData); };
    reader.readAsDataURL(file);
});

inputImageUrl.addEventListener('input', () => {
    uploadedImageData = null; inputProductFile.value = '';
    setPreview(inputImageUrl.value.trim() || null);
});

function openAddModal() {
    editingId = null;
    addProductError.textContent = '';
    uploadedImageData = null;
    inputProductId.value = getNextId();
    inputName.value = inputStock.value = inputPrice.value = inputImageUrl.value = '';
    inputProductFile.value = '';
    document.querySelector('#addProductModal .modal-header h3').textContent = 'Add Product';
    document.getElementById('addProductSave').innerHTML = "<i class='bx bx-plus'></i> Add Product";
    setPreview(null);
    addProductModal.classList.add('active');
    inputName.focus();
}

function openEditModal(productId) {
    const product = products.find(p => p.id === productId);
    if (!product) return;
    editingId = productId;
    addProductError.textContent = '';
    uploadedImageData = product.image?.startsWith('data:') ? product.image : null;
    inputProductId.value = product.id;
    inputName.value      = product.name;
    inputStock.value     = product.stock;
    inputPrice.value     = product.price;
    inputImageUrl.value  = product.image?.startsWith('data:') ? '' : (product.image || '');
    inputProductFile.value = '';
    document.querySelector('#addProductModal .modal-header h3').textContent = 'Edit Product';
    document.getElementById('addProductSave').innerHTML = "<i class='bx bxs-save'></i> Save Changes";
    setPreview(product.image || null);
    addProductModal.classList.add('active');
    inputName.focus();
}

function closeAddModal() {
    addProductModal.classList.remove('active');
    addProductError.textContent = '';
    uploadedImageData = null;
    editingId = null;
}

document.getElementById('addProductBtn').addEventListener('click', openAddModal);
document.getElementById('addProductModalClose').addEventListener('click', closeAddModal);
document.getElementById('addProductCancel').addEventListener('click', closeAddModal);
addProductModal.addEventListener('click', (e) => { if (e.target === addProductModal) closeAddModal(); });

// ── Save (add + edit) ──
document.getElementById('addProductSave').addEventListener('click', async () => {
    const name  = inputName.value.trim();
    const stock = parseInt(inputStock.value, 10);
    const price = parseFloat(inputPrice.value);

    if (!name)                     { addProductError.textContent = 'Product name is required.';   return; }
    if (isNaN(stock) || stock < 0) { addProductError.textContent = 'Enter a valid stock quantity.'; return; }
    if (isNaN(price) || price < 0) { addProductError.textContent = 'Enter a valid price.';          return; }

    const image = uploadedImageData || inputImageUrl.value.trim() || '';
    const id    = editingId || inputProductId.value;

    try {
        const res  = await fetch(PRODUCTS_URL, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ action: 'save', id, name, stock, price, image }),
        });
        const data = await res.json();

        if (data.success) {
            window.location.reload();
        } else {
            addProductError.textContent = data.error || 'Could not save product.';
        }
    } catch {
        addProductError.textContent = 'Network error.';
    }
});

// ── Delete ──
async function handleDelete(productId) {
    const product = products.find(p => p.id === productId);
    if (!product) return;
    if (!confirm(`Delete "${product.name}"?`)) return;

    try {
        const res  = await fetch(PRODUCTS_URL, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ action: 'delete', id: productId }),
        });
        const data = await res.json();
        if (data.success) { window.location.reload(); }
        else { alert(data.error || 'Could not delete product.'); }
    } catch {
        alert('Network error.');
    }
}

// ── Load products ──
async function loadProducts() {
    try {
        const res = await fetch('/BakeryOrderingSystem/PHP/products.php');
        const data = await res.json();
        products = Array.isArray(data) ? data : [];
        renderProducts();
    } catch {
        document.getElementById('productsTableBody').innerHTML = '<tr><td colspan="7">Could not load products.</td></tr>';
    }
}

// ── Logout ──
const logoutModal     = document.getElementById('logoutModal');
const logoutBtn       = document.getElementById('logoutBtn');
const logoutCancelBtn = document.getElementById('logoutCancelBtn');

logoutBtn.addEventListener('click',       () => logoutModal.classList.add('active'));
logoutCancelBtn.addEventListener('click', () => logoutModal.classList.remove('active'));
logoutModal.addEventListener('click', (e) => { if (e.target === logoutModal) logoutModal.classList.remove('active'); });

renderProducts();
