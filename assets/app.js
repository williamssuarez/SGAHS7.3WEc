/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';
import 'overlayscrollbars/styles/overlayscrollbars.css';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.css';
import 'datatables.net-bs5/css/dataTables.bootstrap5.min.css';
import 'select2/dist/css/select2.min.css';
import 'select2-bootstrap-5-theme/dist/select2-bootstrap-5-theme.min.css';
import 'jsvectormap/dist/jsvectormap.css';
import 'apexcharts/dist/apexcharts.css';
import 'admin-lte/dist/css/adminlte.css';

// js goes here
import $ from 'jquery';
import { OverlayScrollbars } from 'overlayscrollbars';
import 'bootstrap';
import 'admin-lte/dist/js/adminlte';
import 'datatables.net-bs5';
import 'select2';
import { Sortable } from 'sortablejs';
import 'jsvectormap';
import 'apexcharts';
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import timeGridPlugin from '@fullcalendar/timegrid';
import esLocale from '@fullcalendar/core/locales/es';

$(document).ready(function () {
    $('.dtHere').DataTable({
        pageLength: 5,
        lengthMenu: [[5, 10, 20, 50, -1], [5, 10, 20, 50, 'All']],
        language: {
            decimal: "",
            emptyTable: "No hay datos disponibles en la tabla",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 a 0 de 0 registros",
            infoFiltered: "(filtrado de _MAX_ registros totales)",
            lengthMenu: "Mostrar _MENU_ registros",
            loadingRecords: "Cargando...",
            processing: "Procesando...",
            search: "Buscar:",
            zeroRecords: "No se encontraron resultados",
        }
    });

    //Select 2 with search
    $('#dt-length-0').select2({
        //theme: "bootstrap-5", // Use the installed Bootstrap 5 theme
        width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
        placeholder: $(this).data('placeholder'),
        language: "es",
        minimumResultsForSearch: Infinity,
    });

    //Select 2 with search
    $('.srchSelect').select2({
        //theme: "bootstrap-5", // Use the installed Bootstrap 5 theme
        width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
        placeholder: $(this).data('placeholder'),
        language: "es"
        // search is available by default
    });

    //Select 2 without search
    $('.noSrchSelect').select2({
        theme: "bootstrap-5", // Use the installed Bootstrap 5 theme
        //width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
        //placeholder: $(this).data('placeholder'),
        // 💡 This option hides the search box
        minimumResultsForSearch: Infinity,
        language: "es"
    });
});

new Sortable(document.querySelector('.connectedSortable'), {
    group: 'shared',
    handle: '.card-header',
});

const cardHeaders = document.querySelectorAll('.connectedSortable .card-header');
cardHeaders.forEach((cardHeader) => {
    cardHeader.style.cursor = 'move';
});

/* Overlayscrollbars config start */
const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';

document.addEventListener('DOMContentLoaded', function () {
    const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);

    // 💡 No need to check for a global variable; just use the imported function
    if (sidebarWrapper) {//check
        OverlayScrollbars(sidebarWrapper, {
            scrollbars: {
                theme: 'os-theme-light',
                autoHide: 'leave',
                clickScroll: true,
            },
        });
    }
});
/* Overlayscrollbars config end */

// World map by jsVectorMap
new jsVectorMap({
    selector: '#world-map',
    map: 'world',
});

// Sparkline charts
const option_sparkline1 = {
    series: [
        {
            data: [1000, 1200, 920, 927, 931, 1027, 819, 930, 1021],
        },
    ],
    chart: {
        type: 'area',
        height: 50,
        sparkline: {
            enabled: true,
        },
    },
    stroke: {
        curve: 'straight',
    },
    fill: {
        opacity: 0.3,
    },
    yaxis: {
        min: 0,
    },
    colors: ['#DCE6EC'],
};

const sparkline1 = new ApexCharts(document.querySelector('#sparkline-1'), option_sparkline1);
sparkline1.render();

const option_sparkline2 = {
    series: [
        {
            data: [515, 519, 520, 522, 652, 810, 370, 627, 319, 630, 921],
        },
    ],
    chart: {
        type: 'area',
        height: 50,
        sparkline: {
            enabled: true,
        },
    },
    stroke: {
        curve: 'straight',
    },
    fill: {
        opacity: 0.3,
    },
    yaxis: {
        min: 0,
    },
    colors: ['#DCE6EC'],
};

const sparkline2 = new ApexCharts(document.querySelector('#sparkline-2'), option_sparkline2);
sparkline2.render();

const option_sparkline3 = {
    series: [
        {
            data: [15, 19, 20, 22, 33, 27, 31, 27, 19, 30, 21],
        },
    ],
    chart: {
        type: 'area',
        height: 50,
        sparkline: {
            enabled: true,
        },
    },
    stroke: {
        curve: 'straight',
    },
    fill: {
        opacity: 0.3,
    },
    yaxis: {
        min: 0,
    },
    colors: ['#DCE6EC'],
};

const sparkline3 = new ApexCharts(document.querySelector('#sparkline-3'), option_sparkline3);
sparkline3.render();

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

/*Color Mode Toggler Start*/
(() => {
    "use strict";

    const storedTheme = localStorage.getItem("theme");

    const getPreferredTheme = () => {
        if (storedTheme) {
            return storedTheme;
        }

        return window.matchMedia("(prefers-color-scheme: dark)").matches
            ? "dark"
            : "light";
    };

    const setTheme = function (theme) {
        if (
            theme === "auto" &&
            window.matchMedia("(prefers-color-scheme: dark)").matches
        ) {
            document.documentElement.setAttribute("data-bs-theme", "dark");
        } else {
            document.documentElement.setAttribute("data-bs-theme", theme);
        }
    };

    setTheme(getPreferredTheme());

    const showActiveTheme = (theme, focus = false) => {
        const themeSwitcher = document.querySelector("#bd-theme");

        if (!themeSwitcher) {
            return;
        }

        const themeSwitcherText = document.querySelector("#bd-theme-text");
        const activeThemeIcon = document.querySelector(".theme-icon-active i");
        const btnToActive = document.querySelector(
            `[data-bs-theme-value="${theme}"]`
        );
        const svgOfActiveBtn = btnToActive.querySelector("i").getAttribute("class");

        for (const element of document.querySelectorAll("[data-bs-theme-value]")) {
            element.classList.remove("active");
            element.setAttribute("aria-pressed", "false");
        }

        btnToActive.classList.add("active");
        btnToActive.setAttribute("aria-pressed", "true");
        activeThemeIcon.setAttribute("class", svgOfActiveBtn);
        const themeSwitcherLabel = `${themeSwitcherText.textContent} (${btnToActive.dataset.bsThemeValue})`;
        themeSwitcher.setAttribute("aria-label", themeSwitcherLabel);

        if (focus) {
            themeSwitcher.focus();
        }
    };

    window
        .matchMedia("(prefers-color-scheme: dark)")
        .addEventListener("change", () => {
            if (storedTheme !== "light" || storedTheme !== "dark") {
                setTheme(getPreferredTheme());
            }
        });

    window.addEventListener("DOMContentLoaded", () => {
        showActiveTheme(getPreferredTheme());

        for (const toggle of document.querySelectorAll("[data-bs-theme-value]")) {
            toggle.addEventListener("click", () => {
                const theme = toggle.getAttribute("data-bs-theme-value");
                localStorage.setItem("theme", theme);
                setTheme(theme);
                showActiveTheme(theme, true);
            });
        }
    });
})();
/*Color Mode Toggler End*/
