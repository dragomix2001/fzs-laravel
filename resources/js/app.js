import './bootstrap';
import $ from 'jquery';
import * as bootstrap from 'bootstrap';

window.$ = window.jQuery = $;
window.bootstrap = bootstrap;

$(document).ready(function() {
    initializeDropdowns();
});

function initializeDropdowns() {
    document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function(dropdownToggle) {
        new bootstrap.Dropdown(dropdownToggle);
    });
}

if (typeof window.DataTable !== 'undefined') {
    $.fn.DataTable = window.DataTable;
}
