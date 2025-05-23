// Tab switching
document.querySelectorAll('.sidebar .nav-link').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelectorAll('.sidebar .nav-link').forEach(l => l.classList.remove('active'));
        this.classList.add('active');
        document.querySelectorAll('.tab-pane').forEach(tab => tab.classList.remove('active'));
        document.getElementById(this.dataset.tab).classList.add('active');
    });
});

// Chart.js - Dashboard visits chart
if (document.getElementById('visitsChart')) {
    const visitsLabels = window.visitsLabels || [];
    const visitsData = window.visitsData || [];
    const ctx = document.getElementById('visitsChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: visitsLabels,
            datasets: [{
                label: 'Visits',
                data: visitsData,
                fill: true,
                backgroundColor: 'rgba(63, 94, 251, 0.1)',
                borderColor: '#6f52a3',
                tension: 0.3,
                pointBackgroundColor: '#ff6b81',
                pointRadius: 5
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
}

// Sidebar toggle for mobile
window.toggleSidebar = function() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('open');
    document.body.classList.toggle('sidebar-open');
    if (sidebar.classList.contains('open')) {
        document.addEventListener('click', closeSidebarOnClickOutside);
    } else {
        document.removeEventListener('click', closeSidebarOnClickOutside);
    }
};
function closeSidebarOnClickOutside(e) {
    const sidebar = document.querySelector('.sidebar');
    const toggle = document.querySelector('.sidebar-toggle');
    if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
        sidebar.classList.remove('open');
        document.body.classList.remove('sidebar-open');
        document.removeEventListener('click', closeSidebarOnClickOutside);
    }
}