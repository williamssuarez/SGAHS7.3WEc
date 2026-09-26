import { Controller } from '@hotwired/stimulus';
import { Calendar } from '@fullcalendar/core';
import timeGridPlugin from '@fullcalendar/timegrid';
import esLocale from '@fullcalendar/core/locales/es';

export default class extends Controller {
    static targets = ['calendar'];
    static values = { events: String };

    connect() {
        this.calendar = new Calendar(this.calendarTarget, {
            plugins: [timeGridPlugin],
            initialView: 'timeGridWeek',
            locale: esLocale,
            events: this.eventsValue,
            allDaySlot: false,
            slotMinTime: '00:00:00', // Adjust to your clinic's opening hours
            slotMaxTime: '23:59:59',
            headerToolbar: {
                left: '',
                center: '',
                right: '' // Remove navigation buttons since it's a static template
            },
            dayHeaderFormat: { weekday: 'long' }, // Shows "Lunes", hides the specific date
            eventClick: function(info) {
                if (info.event.url) {
                    window.location.href = info.event.url;
                    info.jsEvent.preventDefault();
                }
            }
        });

        // Bootstrap Tabs bug: FullCalendar renders incorrectly if initialized inside a hidden tab.
        // We must tell it to resize when the tab becomes visible.
        const tabEl = document.querySelector('a[href="#tab-calendar"]');
        if (tabEl) {
            tabEl.addEventListener('shown.bs.tab', () => {
                this.calendar.render();
            });
        }
    }
}
