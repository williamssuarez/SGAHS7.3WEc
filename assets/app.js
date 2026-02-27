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
import '@fortawesome/fontawesome-free/css/all.min.css'
import 'datatables.net-bs5/css/dataTables.bootstrap5.min.css';
import 'datatables.net-responsive-bs5/css/responsive.bootstrap5.css';
import 'select2/dist/css/select2.min.css';
import 'select2-bootstrap-5-theme/dist/select2-bootstrap-5-theme.min.css';
import 'jsvectormap/dist/jsvectormap.css';
import 'apexcharts/dist/apexcharts.css';
import 'admin-lte/dist/css/adminlte.css';
import 'viewerjs/dist/viewer.css';

// js goes here
import './stimulus_bootstrap'
import $ from 'jquery';
import {OverlayScrollbars} from 'overlayscrollbars';
import * as bootstrap from 'bootstrap';
import '@fortawesome/fontawesome-free/js/all';
import 'admin-lte/dist/js/adminlte';
import 'datatables.net-bs5';
import 'datatables.net-responsive-bs5';
import 'select2';
import {Sortable} from 'sortablejs';
import 'jsvectormap';
import 'apexcharts';
import {Calendar} from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import timeGridPlugin from '@fullcalendar/timegrid';
import esLocale from '@fullcalendar/core/locales/es';
import Swal from 'sweetalert2';
import Viewer from 'viewerjs';

/* SWEETALERTS START */
function softDeleteRecord(deleteUrl, csrfToken) {
    Swal.fire({
        title: '¿Eliminar Registro?',
        text: 'Está a punto de marcar este registro como inactivo. ¿Desea continuar?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, Eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            //loading
            Swal.fire({
                title: 'Eliminando...',
                text: 'Por favor espere mientras se procesa la solicitud.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: deleteUrl, // Use the URL passed as the first argument
                type: 'POST',
                data: {
                    _token: csrfToken
                },
                success: function(response) {
                    Swal.fire({
                        title: '¡Eliminado!',
                        text: 'El registro ha sido marcado como inactivo.',
                        icon: 'success',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'Ok'
                    }).then(() => {
                        window.location.reload();
                    });
                },
                error: function(xhr, status, error) {
                    Swal.fire(
                        'Error!',
                        'No se pudo completar la operación: ' + xhr.responseText,
                        'error'
                    );
                }
            });
        }
    });
}
window.softDeleteRecord = softDeleteRecord;
function deleteFile(deleteUrl, csrfToken) {
    Swal.fire({
        title: '¿Eliminar Archivo?',
        text: 'Está a punto de eliminar un archivo, esta accion no se puede revertir. ¿Desea continuar?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, Eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            //loading
            Swal.fire({
                title: 'Eliminando...',
                text: 'Por favor espere mientras se procesa la solicitud.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: deleteUrl, // Use the URL passed as the first argument
                type: 'POST',
                data: {
                    _token: csrfToken
                },
                success: function(response) {
                    Swal.fire({
                        title: '¡Eliminado!',
                        text: 'El archivo ha sido eliminado definitivamente.',
                        icon: 'success',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'Ok'
                    }).then(() => {
                        window.location.reload();
                    });
                },
                error: function(xhr, status, error) {
                    Swal.fire(
                        'Error!',
                        'No se pudo completar la operación: ' + xhr.responseText,
                        'error'
                    );
                }
            });
        }
    });
}
window.deleteFile = deleteFile;
/* SWEETALERTS END */

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
        },
        responsive: true,
    });

    //Select 2 with ajax search
    $('.ajaxSrchSelect').select2({
        theme: "bootstrap-5", // Use the installed Bootstrap 5 theme
        width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
        placeholder: $(this).data('placeholder'),
        language: "es",
        ajax: {
            url: '/paciente/autocomplete-paciente', // Match the route name/path
            dataType: 'json',
            delay: 250, // Wait 250ms after typing stops before sending request
            data: function (params) {
                return {
                    q: params.term // search term
                };
            },
            processResults: function (data) {
                return {
                    results: data.results
                };
            },
            cache: true
        },
        minimumInputLength: 3, // Only search after 3 characters
    });

    //Select 2 with search
    $('.srchSelect').select2({
        theme: "bootstrap-5", // Use the installed Bootstrap 5 theme
        width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
        placeholder: $(this).data('placeholder'),
        language: "es"
        // search is available by default
    });

    //Select 2 without search
    $('.noSrchSelect').select2({
        theme: "bootstrap-5", // Use the installed Bootstrap 5 theme bruh
        width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
        placeholder: $(this).data('placeholder'),
        // 💡 This option hides the search box
        minimumResultsForSearch: Infinity,
        language: "es"
    });

    //make inputs number only
    $('.number-only').on('keypress keyup blur', function (e) {
        // Remove non-digit characters if pasted/typed
        $(this).val($(this).val().replace(/[^\d].+/, ""));

        // Prevent key presses that are not digits (0-9)
        if ((e.which < 48 || e.which > 57)) {
            e.preventDefault();
        }
    });

    // --- Start Event Listeners for tabs ---
    let isProgrammaticSwitch = false;
    const tabList = $('#formTab button');
    let currentTabIndex = 0;
    const totalTabs = tabList.length;
    const progressBarInner = $('#form-progress-bar'); // Target the inner bar element
    const progressBarWrapper = progressBarInner.closest('.progress'); // Target the wrapper for aria attributes

    /**
     * Updates the visibility of the navigation buttons based on the current tab.
     */
    function updateNavButtons() {
        const isFirstTab = (currentTabIndex === 0);
        const isLastTab = (currentTabIndex === totalTabs - 1);

        // Hide/Show Previous button
        if (isFirstTab){
            if ($("#prev-tab").is("[disabled]") === false) {
                $('#prev-tab').attr('disabled', 'disabled');
            }
        }

        if (!isFirstTab){
            $('#prev-tab').removeAttr('disabled');
        }

        // Manage Next/Submit button visibility
        if (isLastTab) {
            $('#next-tab').hide();      // Hide "Next"
            $('#submit-form').show();   // Show "Guardar"
        } else {
            $('#next-tab').show();      // Show "Next"
            $('#submit-form').hide();   // Hide "Guardar"
        }
    }

    /**
     * Calculates and updates the progress bar width and aria attributes inside the tab forms.
     */
    function updateProgressBar() {
        //idk how this shit works but it's neat
        // 1. Calculate Percentage
        // (currentTabIndex + 1) because the index is 0-based, and we want to show 100% on the last tab.
        // We subtract 1 from totalTabs because the progress is based on the number of steps *between* tabs.
        const progressSteps = totalTabs - 1;
        // Calculate percentage (clamped between 0 and 100)
        let percentage = (currentTabIndex / progressSteps) * 100;
        percentage = Math.max(0, Math.min(100, percentage)); // Ensure it's between 0 and 100

        // 2. Apply Updates
        progressBarInner.css('width', percentage + '%');

        // Update accessibility attributes
        progressBarWrapper.attr('aria-valuenow', Math.round(percentage));

        // Optional: Update the visual label if desired (Bootstrap 5 requires a label inside the bar)
        // progressBarInner.text(Math.round(percentage) + '%');
    }

    /**
     * Switches to the specified tab index.
     */
    function goToTab(index) {
        if (index >= 0 && index < totalTabs) {
            // 1. SET THE FLAG before initiating the programmatic switch
            isProgrammaticSwitch = true;
            currentTabIndex = index;

            // Get the Bootstrap button element for the new tab
            const newTabButton = tabList.eq(index);

            // Use the Bootstrap JavaScript API to show the tab
            const tab = new bootstrap.Tab(newTabButton[0]);
            tab.show();

            // Update button visibility
            isProgrammaticSwitch = false;
            updateProgressBar();
            updateNavButtons();
        }
    }

    // Initialize on page load (starts on tab 0)
    updateNavButtons();
    updateProgressBar();

    //Prevent free navigation between tabs
    $('#formTab').on('show.bs.tab', 'button', function(e) {
        if (isProgrammaticSwitch === true) {
            // If the flag is set, this event was triggered by our goToTab function.
            // We do NOTHING, allowing the tab switch to complete.
            //console.log('Programmatic switch detected. Allowing tab to show.');
            return;
        }

        // If the flag is FALSE, this was a manual user_internal click (or external, unwanted event).
        e.preventDefault();
        //console.log('Free tab toggle attempted and blocked finely');
    });

    // Handle "Next" button click
    $('#next-tab').on('click', function() {
        // NOTE: You can add form validation logic here before advancing:
        // if (validateCurrentTab() == true) {
        //     goToTab(currentTabIndex + 1);
        // }
        goToTab(currentTabIndex + 1);
    });

    // Handle "Previous" button click
    $('#prev-tab').on('click', function() {
        goToTab(currentTabIndex - 1);
    });

    // Optional: Keep the current index updated if the tab is changed externally
    tabList.on('shown.bs.tab', function (e) {
        const targetId = $(e.target).attr('id'); // e.g., "tab2"
        currentTabIndex = tabList.index(e.target);
        updateNavButtons();
    });
    // ---End of Event Listeners for tabs ---

    // ---Start of Viewer.js definition ---
    const container = document.getElementById('photo-container');
    if (container) {
        // 1. Declare the variable that will hold the Viewer instance
        let viewerInstance;

        // 2. Initialize the Viewer, assigning the instance to the variable
        viewerInstance = new Viewer(container, {
            inline: false,
            title: false,
            navbar: false,
            zIndex: 9999,

            url(image) {
                return image.getAttribute('data-original');
            },

            toolbar: {
                zoomIn: true,
                zoomOut: true,
                oneToOne: true,
                reset: true,
                download: {
                    show: true,
                    title: 'Descargar',
                    content: '<i class="bi bi-download"></i>',
                    click: function () {
                            // This logic is already proven to work:
                            const imageUrl = viewerInstance.image.src;
                            const filename = imageUrl.substring(imageUrl.lastIndexOf('/') + 1);

                            const link = document.createElement('a');
                            link.href = imageUrl;
                            link.download = filename;

                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                        }
                    },

                rotateLeft: true,
                rotateRight: true,
                flipHorizontal: true,
                flipVertical: true,
            },
        });
    }
    // ---End of Viewer.js definition ---
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

//ALL DOMCONTENTLOADED EVENTS GO HERE
document.addEventListener('DOMContentLoaded', function () {
    console.log('IT LOADS CORRECTLY SO IDK WHATS WRONG');
    //OverlayScrollbars
    const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
    if (sidebarWrapper) {//check
        OverlayScrollbars(sidebarWrapper, {
            scrollbars: {
                theme: 'os-theme-light',
                autoHide: 'leave',
                clickScroll: true,
            },
        });
    }

    //fullcalendar
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
