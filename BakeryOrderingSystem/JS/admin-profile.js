// ── Sidebar toggle ──
const sidebar   = document.getElementById('sidebar');
const overlay   = document.getElementById('overlay');
const toggleBtn = document.getElementById('toggleBtn');

toggleBtn.addEventListener('click', () => { sidebar.classList.toggle('open'); overlay.classList.toggle('active'); });
overlay.addEventListener('click',   () => { sidebar.classList.remove('open'); overlay.classList.remove('active'); });

let profile = {};

// ── Render ──
function renderProfile() {
    const fullName = `${profile.first_name || ''} ${profile.last_name || ''}`.trim() || '—';
    const address  = [profile.city, profile.country, profile.postal].filter(Boolean).join(', ') || '—';

    document.getElementById('displayFullName').textContent    = fullName;
    document.getElementById('displayFirstName').textContent   = profile.first_name || '—';
    document.getElementById('displayLastName').textContent    = profile.last_name  || '—';
    document.getElementById('displayDob').textContent         = formatDate(profile.dob);
    document.getElementById('displayEmail').textContent       = profile.email   || '—';
    document.getElementById('displayPhone').textContent       = profile.phone   || '—';
    document.getElementById('displayCountry').textContent     = profile.country || '—';
    document.getElementById('displayCity').textContent        = profile.city    || '—';
    document.getElementById('displayPostal').textContent      = profile.postal  || '—';
    document.getElementById('displayAddressText').textContent = address;
    document.getElementById('topbarName').textContent         = profile.first_name || 'Admin';
    document.getElementById('topbarAvatar').textContent       = (profile.first_name || 'A').charAt(0).toUpperCase();

    const avatarEl = document.getElementById('profileAvatar');
    const topbarAvatarEl = document.getElementById('topbarAvatar');
    if (profile.photo) {
        avatarEl.innerHTML = `<img src="${profile.photo}" alt="Profile photo">`;
        topbarAvatarEl.innerHTML = `<img src="${profile.photo}" alt="Profile photo">`;
    } else {
        const initial = (profile.first_name || 'A').charAt(0).toUpperCase();
        avatarEl.textContent = initial;
        topbarAvatarEl.textContent = initial;
    }
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
    const [y, m, d] = dateStr.split('-');
    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    return `${months[parseInt(m, 10) - 1]} ${parseInt(d, 10)}, ${y}`;
}

// ── Modal helpers ──
function openModal(id)  { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }

document.querySelectorAll('.modal-overlay').forEach(ov => {
    ov.addEventListener('click', (e) => { if (e.target === ov) ov.classList.remove('active'); });
});
document.querySelectorAll('.modal-close, .btn-cancel').forEach(btn => {
    if (btn.dataset.modal) btn.addEventListener('click', () => closeModal(btn.dataset.modal));
});

// ── Photo upload ──
async function savePhoto(dataUri) {
    try {
        const res  = await fetch('/BakeryOrderingSystem/PHP/profile_save.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ type: 'photo', photo: dataUri }),
        });
        const data = await res.json();
        if (!data.success) alert(data.error || 'Could not save photo.');
    } catch { alert('Network error.'); }
}

document.getElementById('editPicBtn').addEventListener('click', () => document.getElementById('avatarInput').click());
document.getElementById('avatarInput').addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = async (ev) => {
        profile.photo = ev.target.result;
        renderProfile();
        await savePhoto(profile.photo);
    };
    reader.readAsDataURL(file);
});

document.getElementById('removePicBtn').addEventListener('click', async () => {
    profile.photo = null;
    renderProfile();
    await savePhoto('');
});

// ── Personal Info ──
document.getElementById('editPersonalBtn').addEventListener('click', () => {
    document.getElementById('inputFirstName').value = profile.first_name || '';
    document.getElementById('inputLastName').value  = profile.last_name  || '';
    document.getElementById('inputDob').value       = profile.dob        || '';
    document.getElementById('inputEmail').value     = profile.email      || '';
    document.getElementById('inputPhone').value     = profile.phone      || '';
    openModal('modalPersonal');
});

document.getElementById('savePersonalBtn').addEventListener('click', async () => {
    const payload = {
        type:       'personal',
        first_name: document.getElementById('inputFirstName').value.trim(),
        last_name:  document.getElementById('inputLastName').value.trim(),
        dob:        document.getElementById('inputDob').value,
        email:      document.getElementById('inputEmail').value.trim(),
        phone:      document.getElementById('inputPhone').value.trim(),
    };

    try {
        const res  = await fetch('/BakeryOrderingSystem/PHP/profile_save.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload),
        });
        const data = await res.json();
        if (data.success) {
            Object.assign(profile, payload);
            renderProfile();
            closeModal('modalPersonal');
        } else { alert(data.error || 'Could not save.'); }
    } catch { alert('Network error.'); }
});

// ── Address ──
document.getElementById('editAddressBtn').addEventListener('click', () => {
    document.getElementById('inputCountry').value = profile.country || '';
    document.getElementById('inputCity').value    = profile.city    || '';
    document.getElementById('inputPostal').value  = profile.postal  || '';
    openModal('modalAddress');
});

document.getElementById('saveAddressBtn').addEventListener('click', async () => {
    const payload = {
        type:    'address',
        country: document.getElementById('inputCountry').value.trim(),
        city:    document.getElementById('inputCity').value.trim(),
        postal:  document.getElementById('inputPostal').value.trim(),
    };

    try {
        const res  = await fetch('/BakeryOrderingSystem/PHP/profile_save.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload),
        });
        const data = await res.json();
        if (data.success) {
            Object.assign(profile, payload);
            renderProfile();
            closeModal('modalAddress');
        } else { alert(data.error || 'Could not save.'); }
    } catch { alert('Network error.'); }
});

// ── Change Password ──
document.getElementById('changePasswordBtn').addEventListener('click', () => {
    document.getElementById('inputCurrentPw').value = '';
    document.getElementById('inputNewPw').value     = '';
    document.getElementById('inputConfirmPw').value = '';
    document.getElementById('pwError').textContent  = '';
    openModal('modalPassword');
});

document.getElementById('savePasswordBtn').addEventListener('click', async () => {
    const errEl   = document.getElementById('pwError');
    const payload = {
        current: document.getElementById('inputCurrentPw').value,
        new:     document.getElementById('inputNewPw').value,
        confirm: document.getElementById('inputConfirmPw').value,
    };

    try {
        const res  = await fetch('/BakeryOrderingSystem/PHP/password_change.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload),
        });
        const data = await res.json();
        if (data.success) { errEl.textContent = ''; closeModal('modalPassword'); alert('Password updated successfully.'); }
        else { errEl.textContent = data.error || 'Could not update password.'; }
    } catch { errEl.textContent = 'Network error.'; }
});

// ── Logout ──
const logoutModal     = document.getElementById('logoutModal');
const logoutBtn       = document.getElementById('logoutBtn');
const logoutCancelBtn = document.getElementById('logoutCancelBtn');

logoutBtn.addEventListener('click',       () => logoutModal.classList.add('active'));
logoutCancelBtn.addEventListener('click', () => logoutModal.classList.remove('active'));
logoutModal.addEventListener('click', (e) => { if (e.target === logoutModal) logoutModal.classList.remove('active'); });

// ── Load profile ──
async function loadProfile() {
    try {
        const res = await fetch('/BakeryOrderingSystem/PHP/profile_get.php');
        profile   = await res.json();
        renderProfile();
    } catch {
        renderProfile();
    }
}

loadProfile();
