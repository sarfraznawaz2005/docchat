function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('main-content');

    if (window.innerWidth <= 768) {
        sidebar.classList.toggle('active');
    } else {
        sidebar.classList.toggle('hidden');
        mainContent.classList.toggle('full-width');
    }
}

// Check screen size on load and resize
function checkScreenSize() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('main-content');

    if (window.innerWidth <= 768) {
        sidebar.classList.remove('hidden');
        sidebar.classList.remove('active');
        mainContent.classList.add('full-width');
    } else {
        sidebar.classList.remove('active');
        sidebar.classList.remove('hidden');
        mainContent.classList.remove('full-width');
    }
}

window.addEventListener('load', checkScreenSize);
window.addEventListener('resize', checkScreenSize);
