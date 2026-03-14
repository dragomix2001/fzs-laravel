import './bootstrap';
import $ from 'jquery';
import * as bootstrap from 'bootstrap';

window.$ = window.jQuery = $;
window.bootstrap = bootstrap;

$(document).ready(function() {
    initializeTooltips();
    initializeDropdowns();
    initializeSidebar();
});

function initializeTooltips() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

function initializeDropdowns() {
    document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function(dropdownToggle) {
        new bootstrap.Dropdown(dropdownToggle);
    });
}

function initializeSidebar() {
    document.querySelectorAll('#side-menu li a').forEach(function(link) {
        link.addEventListener('click', function(e) {
            var parent = this.parentElement;
            var submenu = parent.querySelector('.nav-second-level, .nav-third-level');
            
            if (submenu) {
                e.preventDefault();
                submenu.classList.toggle('d-none');
            }
        });
    });
}

if (typeof window.DataTable !== 'undefined') {
    $.fn.DataTable = window.DataTable;
}
