import './bootstrap';
import $ from 'jquery';
import * as bootstrap from 'bootstrap';

window.$ = window.jQuery = $;
window.bootstrap = bootstrap;

$(document).ready(function() {
    initializeSidebar();
    initializeDropdowns();
});

function initializeSidebar() {
    $('#side-menu li a').on('click', function(e) {
        var $this = $(this);
        var $parent = $this.parent();
        var $submenu = $parent.children('.nav-second-level');
        
        if ($submenu.length > 0) {
            e.preventDefault();
            
            if ($submenu.is(':visible')) {
                $submenu.slideUp(200);
                $this.find('.fa-angle-down').removeClass('fa-angle-up').addClass('fa-angle-down');
            } else {
                $submenu.slideDown(200);
                $this.find('.fa-angle-down').removeClass('fa-angle-down').addClass('fa-angle-up');
            }
        }
    });
    
    $('#side-menu li.active > .nav-second-level').show();
    $('#side-menu li.active > a .fa-angle-down').removeClass('fa-angle-down').addClass('fa-angle-up');
}

function initializeDropdowns() {
    document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function(dropdownToggle) {
        new bootstrap.Dropdown(dropdownToggle);
    });
}

if (typeof window.DataTable !== 'undefined') {
    $.fn.DataTable = window.DataTable;
}
