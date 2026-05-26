
    const menuBtn = document.getElementById('menuBtn');
    const sideMenu = document.getElementById('sideMenu');
    const closeMenu = document.getElementById('closeMenu');
    const overlay = document.getElementById('overlay');
    const logoutLink = document.getElementById('logoutLink');

    function openMenu() {
    sideMenu.classList.add('active');
    sideMenu.setAttribute('aria-hidden', 'false');
    overlay.style.display = 'block';
}

    function closeAll() {
    sideMenu.classList.remove('active');
    sideMenu.setAttribute('aria-hidden', 'true');
    overlay.style.display = 'none';
}

    if (menuBtn && closeMenu && overlay) {
    menuBtn.addEventListener('click', openMenu);
    closeMenu.addEventListener('click', closeAll);
    overlay.addEventListener('click', closeAll);
}

    if (logoutLink) {
    logoutLink.addEventListener('click', function(event) {
        if (!window.confirm('Est tu sure que tu veux te deconnecter ?')) {
            event.preventDefault();
        }
    });
}
