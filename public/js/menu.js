
    const menuBtn = document.getElementById('menuBtn');
    const closeMenuBtn = document.getElementById('closeMenu');
    const sideMenu = document.getElementById('sideMenu');
    const overlay = document.getElementById('overlay');

    /* OUVRIR */
    menuBtn.addEventListener('click', () => {
    sideMenu.classList.add('active');
    overlay.style.display = 'block';
});

    /* FERMER */
    closeMenuBtn.addEventListener('click', () => {
    sideMenu.classList.remove('active');
    overlay.style.display = 'none';
});

    /* CLIQUER DEHORS */
    overlay.addEventListener('click', () => {
    sideMenu.classList.remove('active');
    overlay.style.display = 'none';
});
