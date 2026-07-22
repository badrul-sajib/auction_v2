import './bootstrap';
import Alpine from 'alpinejs';
import ApexCharts from 'apexcharts';
import Swal from 'sweetalert2';

window.Alpine = Alpine;
window.ApexCharts = ApexCharts;
window.Swal = Swal;

// Fire a SweetAlert toast for any flashed message set on window.__flash
function showFlashToast() {
    if (!window.__flash || !window.__flash.message) {
        return;
    }
    const { type = 'success', message } = window.__flash;
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: type,
        title: message,
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true,
    });
    window.__flash = null;
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', showFlashToast);
} else {
    showFlashToast();
}

Alpine.start();

// Dashboard charts
document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('#chartOne')) {
        import('./components/chart/chart-1').then(module => module.initChartOne());
    }
    if (document.querySelector('#chartTwo')) {
        import('./components/chart/chart-2').then(module => module.initChartTwo());
    }
});
