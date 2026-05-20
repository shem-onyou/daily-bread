document.getElementById('loginForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const role = document.getElementById('role').value;
    if (role === 'admin') {
        window.location.href = '../HTML/admin-dashboard.html';
    } else {
        window.location.href = '../HTML/home.html';
    }
});
