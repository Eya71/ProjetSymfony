const menuBtn = document.getElementById("menuBtn");
const sideMenu = document.getElementById("sideMenu");
const closeMenu = document.getElementById("closeMenu");
const overlay = document.getElementById("overlay");

function openMenu() {
  if (!sideMenu) return;
  document.body.classList.add("sidebar-open");
  sideMenu.classList.add("active");
  sideMenu.setAttribute("aria-hidden", "false");
  closeOverlayOnly();
}

function closeAll() {
  document.body.classList.remove("sidebar-open", "menu-open");
  document.body.style.removeProperty("overflow");

  if (sideMenu) {
    sideMenu.classList.remove("active");
    sideMenu.setAttribute("aria-hidden", "true");
  }

  closeOverlayOnly();
}

function closeOverlayOnly() {
  if (!overlay) return;
  overlay.removeAttribute("data-open");
  overlay.classList.remove("active");
  overlay.style.display = "none";
  overlay.style.opacity = "0";
  overlay.style.visibility = "hidden";
  overlay.style.pointerEvents = "none";
  overlay.style.background = "transparent";
  overlay.style.zIndex = "-1";
}

window.importySidebarClose = closeAll;
window.importySidebarOpen = openMenu;
closeAll();
window.addEventListener("pageshow", closeAll);

if (menuBtn) {
  menuBtn.addEventListener("click", openMenu);
}

if (closeMenu) {
  closeMenu.addEventListener("click", closeAll);
}

if (overlay) {
  overlay.addEventListener("click", closeAll);
}

const iconItems = document.querySelectorAll(".icon-item");

iconItems.forEach(item => {
  item.addEventListener("click", function(e) {

    
    if (!item.classList.contains("login-btn")) {
      e.preventDefault();
      openMenu();
    }

  });
});
