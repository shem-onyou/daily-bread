const PAGE_SIZE = 6;

function paginate(items, page) {
    const total = Math.ceil(items.length / PAGE_SIZE) || 1;
    const current = Math.min(Math.max(1, page), total);
    const start = (current - 1) * PAGE_SIZE;
    return { items: items.slice(start, start + PAGE_SIZE), current, total };
}

function renderPagination(containerId, current, total, onPageChange) {
    const container = document.getElementById(containerId);
    if (!container) return;
    container.innerHTML = '';
    if (total <= 1) return;

    function btn(label, page, disabled, active) {
        const b = document.createElement('button');
        b.className = 'page-btn' + (active ? ' active' : '') + (disabled ? ' disabled' : '');
        b.textContent = label;
        b.disabled = disabled;
        if (!disabled) b.addEventListener('click', () => onPageChange(page));
        return b;
    }

    container.appendChild(btn('←', current - 1, current === 1, false));

    const range = new Set([1, total, current - 1, current, current + 1].filter(p => p >= 1 && p <= total));
    let prev = 0;
    [...range].sort((a, b) => a - b).forEach(p => {
        if (prev && p - prev > 1) {
            const dots = document.createElement('span');
            dots.className = 'page-dots';
            dots.textContent = '…';
            container.appendChild(dots);
        }
        container.appendChild(btn(p, p, false, p === current));
        prev = p;
    });

    container.appendChild(btn('→', current + 1, current === total, false));
}
