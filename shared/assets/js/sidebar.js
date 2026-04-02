document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const toggle = document.getElementById('sidebarToggle');
    const overlay = document.getElementById('sidebarOverlay');

    if (toggle && sidebar && overlay) {
        function openSidebar() {
            sidebar.classList.add('open');
            overlay.classList.add('visible');
        }

        function closeSidebar() {
            sidebar.classList.remove('open');
            overlay.classList.remove('visible');
        }

        toggle.addEventListener('click', () => {
            sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
        });

        overlay.addEventListener('click', closeSidebar);

        window.addEventListener('resize', () => {
            if (window.innerWidth > 1024) closeSidebar();
        });
    }
});
