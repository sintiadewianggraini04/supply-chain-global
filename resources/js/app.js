import './bootstrap';

// Bootstrap 5
import 'bootstrap';

// Chart.js
import Chart from 'chart.js/auto';

// Leaflet.js
import L from 'leaflet';

// Supaya Chart dan Leaflet bisa dipakai di file Blade
window.Chart = Chart;
window.L = L;

console.log(
    'Global Supply Chain dashboard loaded'
);