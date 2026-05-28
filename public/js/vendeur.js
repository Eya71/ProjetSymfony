document.addEventListener('DOMContentLoaded', function () {
    const menuBtn = document.getElementById('menuBtn');
    const closeMenu = document.getElementById('closeMenu');
    const sideMenu = document.getElementById('sideMenu');
    const overlay = document.getElementById('overlay');

    if (!menuBtn || !closeMenu || !sideMenu || !overlay) {
        console.log('Un élément du menu est introuvable');
        return;
    }

    menuBtn.addEventListener('click', function () {
        sideMenu.classList.add('active');
        overlay.classList.add('active');
        sideMenu.setAttribute('aria-hidden', 'false');
    });

    closeMenu.addEventListener('click', function () {
        sideMenu.classList.remove('active');
        overlay.classList.remove('active');
        sideMenu.setAttribute('aria-hidden', 'true');
    });

    overlay.addEventListener('click', function () {
        sideMenu.classList.remove('active');
        overlay.classList.remove('active');
        sideMenu.setAttribute('aria-hidden', 'true');
    });
});