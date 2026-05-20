function escapeHTML(str) {
    return String(str)
        .replace(/&/g,  '&amp;')
        .replace(/</g,  '&lt;')
        .replace(/>/g,  '&gt;')
        .replace(/"/g,  '&quot;')
        .replace(/'/g,  '&#x27;')
        .replace(/\//g, '&#x2F;');
}

function sanitizeText(value) {
    return String(value).replace(/\0/g, '').trim();
}

function isValidName(value) {
    return /^[A-Za-zÀ-ÖØ-öø-ÿ0-9\s'\-,.]{1,100}$/.test(value.trim());
}

function isValidEmail(value) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(value.trim());
}

function isValidPhone(value) {
    return /^[+\d\s\-().]{7,20}$/.test(value.trim());
}

function isValidPostal(value) {
    return /^[A-Za-z0-9\s\-]{3,10}$/.test(value.trim());
}

function isValidDate(value) {
    return /^\d{4}-\d{2}-\d{2}$/.test(value) && !isNaN(Date.parse(value));
}

function isValidProductId(value) {
    return /^PRD-\d{3,6}$/.test(value);
}

function isValidOrderId(value) {
    return /^ORD-\d{3,6}$/.test(value);
}

function isValidStatus(value) {
    return ['pending', 'completed', 'cancelled'].includes(value);
}
