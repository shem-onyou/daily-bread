// ── Modal helpers ──
function openModal(id)  { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }

document.querySelectorAll('.modal-overlay').forEach(ov => {
    ov.addEventListener('click', (e) => { if (e.target === ov) ov.classList.remove('active'); });
});
document.querySelectorAll('.modal-close, .btn-modal-cancel').forEach(btn => {
    if (btn.dataset.modal) btn.addEventListener('click', () => closeModal(btn.dataset.modal));
});

// ── Photo upload ──
async function savePhoto(dataUri) {
    try {
        const res  = await fetch(PROFILE_URL, {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ type: 'photo', photo: dataUri }),
        });
        const data = await res.json();
        if (!data.success) alert(data.error || 'Could not save photo.');
    } catch { alert('Network error.'); }
}

function setAvatar(src) {
    const el = document.getElementById('profileAvatar');
    if (src) {
        el.innerHTML = `<img src="${src}" alt="Profile photo">`;
    } else {
        el.innerHTML = '';
        el.textContent = el.dataset.initial;
    }
}

// Store initial letter for fallback
document.getElementById('profileAvatar').dataset.initial =
    document.getElementById('profileAvatar').textContent.trim() ||
    document.getElementById('displayFullName').textContent.trim().charAt(0).toUpperCase() || '?';

document.getElementById('editPicBtn').addEventListener('click', () => document.getElementById('avatarInput').click());
document.getElementById('avatarInput').addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (!file || !file.type.startsWith('image/')) return;
    const reader = new FileReader();
    reader.onload = async (ev) => {
        setAvatar(ev.target.result);
        await savePhoto(ev.target.result);
    };
    reader.readAsDataURL(file);
});

document.getElementById('removePicBtn').addEventListener('click', async () => {
    setAvatar(null);
    await savePhoto('');
});

// ── Personal Info ──
document.getElementById('editPersonalBtn').addEventListener('click', () => openModal('modalPersonal'));

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
        const res  = await fetch(PROFILE_URL, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
        const data = await res.json();
        if (data.success) {
            const full = `${payload.first_name} ${payload.last_name}`.trim();
            document.getElementById('displayFullName').textContent  = full;
            document.getElementById('displayFirstName').textContent = payload.first_name || '—';
            document.getElementById('displayLastName').textContent  = payload.last_name  || '—';
            document.getElementById('displayEmail').textContent     = payload.email      || '—';
            document.getElementById('displayPhone').textContent     = payload.phone      || '—';
            if (payload.dob) {
                const [y,m,d] = payload.dob.split('-');
                const months  = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                document.getElementById('displayDob').textContent = `${months[parseInt(m)-1]} ${parseInt(d)}, ${y}`;
            }
            closeModal('modalPersonal');
        } else { alert(data.error || 'Could not save.'); }
    } catch { alert('Network error.'); }
});

// ── Address ──
document.getElementById('editAddressBtn').addEventListener('click', () => openModal('modalAddress'));

document.getElementById('saveAddressBtn').addEventListener('click', async () => {
    const payload = {
        type:    'address',
        country: document.getElementById('inputCountry').value.trim(),
        city:    document.getElementById('inputCity').value.trim(),
        postal:  document.getElementById('inputPostal').value.trim(),
    };
    try {
        const res  = await fetch(PROFILE_URL, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
        const data = await res.json();
        if (data.success) {
            document.getElementById('displayCountry').textContent     = payload.country || '—';
            document.getElementById('displayCity').textContent        = payload.city    || '—';
            document.getElementById('displayPostal').textContent      = payload.postal  || '—';
            document.getElementById('displayAddressText').textContent = [payload.city, payload.country, payload.postal].filter(Boolean).join(', ') || '—';
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
        type:    'password',
        current: document.getElementById('inputCurrentPw').value,
        new:     document.getElementById('inputNewPw').value,
        confirm: document.getElementById('inputConfirmPw').value,
    };
    try {
        const res  = await fetch(PROFILE_URL, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
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
