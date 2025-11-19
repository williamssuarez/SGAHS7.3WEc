/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'datatables.net-bs5/css/dataTables.bootstrap5.min.css';
import 'admin-lte/dist/css/adminlte.min.css';

import 'select2/dist/css/select2.min.css';
import 'select2-bootstrap-5-theme/dist/select2-bootstrap-5-theme.min.css';

// js goes here
import $ from 'jquery';
import 'bootstrap';
import 'admin-lte/dist/js/adminlte';
import 'datatables.net-bs5';
import 'select2';

import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import timeGridPlugin from '@fullcalendar/timegrid';
import esLocale from '@fullcalendar/core/locales/es';

$(document).ready(function () {
    $('#dtHere').DataTable();

    // 2. Initialize Select2 with SEARCH available (ID: srchSelect)
    $('.srchSelect').select2({
        //theme: "bootstrap-5", // Use the installed Bootstrap 5 theme
        width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
        placeholder: $(this).data('placeholder'),
        language: "es"
        // search is available by default
    });

    // 3. Initialize Select2 without SEARCH available (ID: noSrchSelect)
    $('.noSrchSelect').select2({
        theme: "bootstrap-5", // Use the installed Bootstrap 5 theme
        //width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
        //placeholder: $(this).data('placeholder'),
        // 💡 This option hides the search box
        minimumResultsForSearch: Infinity,
        language: "es"
    });
});

document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');

    if (calendarEl) {
        var calendar = new Calendar(calendarEl, {
            // 1. Register the plugins you imported
            plugins: [ dayGridPlugin, interactionPlugin, timeGridPlugin ],
            locales: [ esLocale ],

            // 2. Set the initial view and other options
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            locale: 'es',

            // 3. Add sample data
            events: [
                { title: 'Meeting', start: '2025-11-14' },
                { title: 'Project Deadline', start: '2025-11-20', end: '2025-11-22' }
            ]
        });

        // 4. Render the calendar
        calendar.render();
    }
});
