(function () {
    function initNavigation() {
        var body = document.body;
        var mobile = window.matchMedia('(max-width: 1056px)');
        var toggles = document.querySelectorAll('#show-hide-sidebar-toggle, .hamburger');
        var submenus = document.querySelectorAll('.side-menu-toggle');
        var overlay = document.querySelector('.mobile-menu-left-overlay');

        function updateNavigation() {
            var expanded = mobile.matches
                ? body.classList.contains('menu-left-opened')
                : !body.classList.contains('sidebar-hidden');

            for (var i = 0; i < toggles.length; i++) {
                toggles[i].setAttribute('aria-expanded', String(expanded));
                toggles[i].classList.toggle('is-active', expanded);
            }
        }

        function closeMobileMenu() {
            body.classList.remove('menu-left-opened');
            updateNavigation();
        }

        function setSubmenu(button, expanded) {
            var menu = document.getElementById(button.getAttribute('aria-controls'));
            button.setAttribute('aria-expanded', String(expanded));
            button.parentNode.classList.toggle('opened', expanded);
            menu.hidden = !expanded;
        }

        for (var i = 0; i < submenus.length; i++) {
            submenus[i].addEventListener('click', function () {
                var expanded = this.getAttribute('aria-expanded') !== 'true';
                for (var j = 0; j < submenus.length; j++) {
                    setSubmenu(submenus[j], false);
                }
                setSubmenu(this, expanded);
            });
        }

        for (var j = 0; j < toggles.length; j++) {
            toggles[j].addEventListener('click', function () {
                if (mobile.matches) {
                    body.classList.remove('sidebar-hidden');
                    body.classList.toggle('menu-left-opened');
                } else {
                    body.classList.remove('menu-left-opened');
                    body.classList.toggle('sidebar-hidden');
                }
                updateNavigation();
            });
        }

        if (overlay) {
            overlay.addEventListener('click', closeMobileMenu);
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && body.classList.contains('menu-left-opened')) {
                closeMobileMenu();
                document.querySelector('.hamburger').focus();
            }
        });

        window.addEventListener('resize', function () {
            if (mobile.matches) {
                body.classList.remove('sidebar-hidden');
            } else {
                body.classList.remove('menu-left-opened');
            }
            updateNavigation();
        });

        updateNavigation();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initNavigation);
    } else {
        initNavigation();
    }
})();
