import './bootstrap';
import '@fontsource/plus-jakarta-sans';
import 'flatpickr/dist/flatpickr.min.css';

import Alpine from 'alpinejs';
import { createIcons, icons } from 'lucide';
import flatpickr from 'flatpickr';
import Swal from 'sweetalert2';
import Chart from 'chart.js/auto';
import * as XLSX from 'xlsx';

window.Alpine = Alpine;

// Lucide NPM requires passing the icons object
window.lucide = {
    createIcons: (options = {}) => createIcons({ icons, ...options }),
    icons
};
window.flatpickr = flatpickr;
window.Swal = Swal;
window.Chart = Chart;
window.XLSX = XLSX;

// Auto-initialize icons on load
document.addEventListener('DOMContentLoaded', () => {
    window.lucide.createIcons();
});

Alpine.start();
