document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.querySelector('.app-sidebar');
    const toggle = document.querySelector('[data-sidebar-toggle]');
    const overlay = document.querySelector('.sidebar-overlay');

    if (!sidebar || !toggle) return;

    const closeSidebar = () => {
        sidebar.classList.remove('open');
        overlay?.classList.remove('show');
        document.body.classList.remove('sidebar-open');
    };

    toggle.addEventListener('click', () => {
        sidebar.classList.toggle('open');
        overlay?.classList.toggle('show');
        document.body.classList.toggle('sidebar-open');
    });

    overlay?.addEventListener('click', closeSidebar);

    sidebar.querySelectorAll('.sidebar-link').forEach((link) => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 992) closeSidebar();
        });
    });

    document.querySelectorAll('.stat-card, .room-card, .feature-card, .card-modern').forEach((el, i) => {
        el.style.animationDelay = `${i * 0.04}s`;
        el.classList.add('animate-in');
    });
});
