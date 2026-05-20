(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var btn      = document.getElementById('navHamburger');
        var dropdown = document.getElementById('navDropdown');
        if (!btn || !dropdown) return;

        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            var open = dropdown.classList.toggle('open');
            btn.querySelector('i').className = open ? 'bx bx-x' : 'bx bx-menu';
            btn.setAttribute('aria-expanded', open);
        });

        // Close when a link is clicked
        dropdown.querySelectorAll('a').forEach(function (a) {
            a.addEventListener('click', function () {
                dropdown.classList.remove('open');
                btn.querySelector('i').className = 'bx bx-menu';
                btn.setAttribute('aria-expanded', 'false');
            });
        });

        // Close when clicking outside
        document.addEventListener('click', function (e) {
            if (!dropdown.contains(e.target) && e.target !== btn) {
                dropdown.classList.remove('open');
                btn.querySelector('i').className = 'bx bx-menu';
                btn.setAttribute('aria-expanded', 'false');
            }
        });
    });
})();
