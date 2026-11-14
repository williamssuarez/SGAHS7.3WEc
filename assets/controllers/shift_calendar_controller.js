import { Controller } from '@hotwired/stimulus';
import { Calendar } from '@fullcalendar/core';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import esLocale from '@fullcalendar/core/locales/es';
import Swal from 'sweetalert2';

export default class extends Controller {
    static targets = ['calendar', 'specialtyFilter', 'doctorFilter', 'inactiveToggle', 'visibleCount'];
    static values = {
        events: String,
        newUrl: String,
        avatars: { type: Object, default: {} }
    };

    connect() {
        this.allEvents = [];
        this.isNavigating = false;
        this.filterSelects = [];
        this.nativeFilterSelects = [];
        this.onTabShown = this.handleTabShown.bind(this);
        this.onCalendarKeyDown = this.handleCalendarKeyDown.bind(this);

        this.calendar = new Calendar(this.calendarTarget, {
            plugins: [timeGridPlugin, interactionPlugin],
            initialView: 'timeGridWeek',
            locale: esLocale,
            firstDay: 1,
            allDaySlot: false,
            slotMinTime: '00:00:00',
            slotMaxTime: '24:00:00',
            scrollTime: '07:00:00',
            slotDuration: '01:00:00',
            snapDuration: '00:30:00',
            height: 600,
            expandRows: true,
            headerToolbar: false,
            dayHeaderFormat: { weekday: 'long' },
            selectable: true,
            selectMirror: true,
            unselectAuto: true,
            longPressDelay: 350,
            selectLongPressDelay: 350,
            events: (_fetchInfo, successCallback) => {
                successCallback(this.getFilteredEvents());
            },
            eventDidMount: (info) => {
                const props = info.event.extendedProps || {};
                info.el.tabIndex = 0;
                info.el.setAttribute('role', 'button');
                info.el.setAttribute(
                    'aria-label',
                    `${props.doctorName || ''}, ${props.especialidadName || ''}, ${props.dayLabel || ''} de ${props.startFormatted || ''} a ${props.endFormatted || ''}${props.isActive ? '' : ' (Inactivo)'}`
                );
            },
            eventClick: (info) => {
                info.jsEvent.preventDefault();
                this.openShiftModal(info.event);
            },
            select: (info) => {
                this.navigateToNewFromRange(info.start, info.end);
            },
            dateClick: (info) => {
                if (this.isNavigating || Swal.isVisible()) {
                    return;
                }
                const start = info.date;
                const end = new Date(start.getTime() + 60 * 60 * 1000);
                this.navigateToNewFromRange(start, end);
            }
        });

        this.calendar.render();
        this.initFilterSelects();
        this.loadEvents();

        this.calendarTarget.addEventListener('keydown', this.onCalendarKeyDown);
        document.addEventListener('shown.bs.tab', this.onTabShown);

        if (typeof ResizeObserver !== 'undefined') {
            this.resizeObserver = new ResizeObserver(() => {
                requestAnimationFrame(() => this.calendar?.updateSize());
            });
            this.resizeObserver.observe(this.calendarTarget);
        }
    }

    disconnect() {
        document.removeEventListener('shown.bs.tab', this.onTabShown);
        if (this.hasCalendarTarget) {
            this.calendarTarget.removeEventListener('keydown', this.onCalendarKeyDown);
        }
        for (const $el of this.filterSelects || []) {
            $el.off('.shiftCalendar');
            if ($el.data('select2')) {
                $el.select2('destroy');
            }
        }
        for (const { selectEl, onNativeChange } of this.nativeFilterSelects || []) {
            selectEl.removeEventListener('change', onNativeChange);
        }
        this.filterSelects = [];
        this.nativeFilterSelects = [];
        this.resizeObserver?.disconnect();
        this.calendar?.destroy();
    }

    initFilterSelects() {
        const targets = [];
        if (this.hasSpecialtyFilterTarget) targets.push(this.specialtyFilterTarget);
        if (this.hasDoctorFilterTarget) targets.push(this.doctorFilterTarget);

        const $ = window.jQuery;
        const hasSelect2 = Boolean($ && $.fn && $.fn.select2);

        for (const selectEl of targets) {
            if (hasSelect2) {
                const $el = $(selectEl);
                if (!$el.data('select2')) {
                    $el.select2({
                        theme: 'bootstrap-5',
                        width: '100%',
                        language: 'es',
                        minimumResultsForSearch: 6
                    });
                }
                $el.off('.shiftCalendar').on('change.shiftCalendar', () => this.applyFilters());
                this.filterSelects.push($el);
            } else {
                const onNativeChange = () => this.applyFilters();
                selectEl.addEventListener('change', onNativeChange);
                this.nativeFilterSelects.push({ selectEl, onNativeChange });
            }
        }
    }

    async loadEvents() {
        if (!this.hasEventsValue) return;

        try {
            const url = new URL(this.eventsValue, window.location.origin);
            url.searchParams.set('include_inactive', '1');
            const response = await fetch(url.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            });
            if (!response.ok) return;

            const data = await response.json();
            if (!Array.isArray(data)) return;

            this.allEvents = data;
            this.populateFilters();
            this.applyFilters();
        } catch (error) {
            console.error('Error loading shifts calendar:', error);
        }
    }

    populateFilters() {
        if (this.hasSpecialtyFilterTarget) {
            const selectedEsp = this.specialtyFilterTarget.value;
            const specialties = new Map();
            for (const ev of this.allEvents) {
                const props = ev.extendedProps || {};
                if (props.especialidadId && props.especialidadName) {
                    specialties.set(String(props.especialidadId), props.especialidadName);
                }
            }
            this.rebuildSelectOptions(this.specialtyFilterTarget, 'Todas las especialidades', specialties, selectedEsp);
        }

        if (this.hasDoctorFilterTarget) {
            const selectedDoc = this.doctorFilterTarget.value;
            const doctors = new Map();
            for (const ev of this.allEvents) {
                const props = ev.extendedProps || {};
                if (props.doctorId && props.doctorName) {
                    doctors.set(String(props.doctorId), props.doctorName);
                }
            }
            this.rebuildSelectOptions(this.doctorFilterTarget, 'Todos los médicos', doctors, selectedDoc);
        }
    }

    rebuildSelectOptions(selectEl, defaultLabel, itemsMap, previousValue) {
        selectEl.textContent = '';
        const defaultOpt = document.createElement('option');
        defaultOpt.value = '';
        defaultOpt.textContent = defaultLabel;
        selectEl.appendChild(defaultOpt);

        const sorted = Array.from(itemsMap.entries()).sort((a, b) => a[1].localeCompare(b[1], 'es'));
        for (const [id, label] of sorted) {
            const opt = document.createElement('option');
            opt.value = id;
            opt.textContent = label;
            if (id === previousValue) {
                opt.selected = true;
            }
            selectEl.appendChild(opt);
        }

        if (window.jQuery && window.jQuery(selectEl).data('select2')) {
            window.jQuery(selectEl).trigger('change.select2');
        }
    }

    applyFilters() {
        const filtered = this.getFilteredEvents();
        if (this.hasVisibleCountTarget) {
            const count = filtered.length;
            this.visibleCountTarget.textContent = `${count} ${count === 1 ? 'turno visible' : 'turnos visibles'}`;
        }
        this.calendar?.refetchEvents();
    }

    getFilteredEvents() {
        const espId = this.hasSpecialtyFilterTarget ? this.specialtyFilterTarget.value : '';
        const docId = this.hasDoctorFilterTarget ? this.doctorFilterTarget.value : '';
        const includeInactive = this.hasInactiveToggleTarget ? this.inactiveToggleTarget.checked : false;

        return this.allEvents.filter((ev) => {
            const props = ev.extendedProps || {};
            if (!includeInactive && !props.isActive) return false;
            if (espId && String(props.especialidadId || '') !== espId) return false;
            if (docId && String(props.doctorId || '') !== docId) return false;
            return true;
        });
    }

    handleTabShown(event) {
        const trigger = event.target;
        if (!trigger) return;
        const targetSelector = trigger.dataset.bsTarget || trigger.getAttribute('href');

        if (targetSelector === '#tab-calendar') {
            requestAnimationFrame(() => this.calendar?.updateSize());
        } else if (targetSelector === '#tab-table' && window.jQuery && window.jQuery.fn.dataTable) {
            requestAnimationFrame(() => {
                try {
                    window.jQuery.fn.dataTable.tables({ visible: true, api: true }).columns.adjust().responsive.recalc();
                } catch (_err) {
                    // Ignorar si no hay tablas visibles aún
                }
            });
        }
    }

    handleCalendarKeyDown(event) {
        if (event.key !== 'Enter' && event.key !== ' ') return;
        const eventEl = event.target.closest('.fc-event');
        if (!eventEl || !this.calendar) return;

        event.preventDefault();
        const match = this.findEventFromElement(eventEl);
        if (match) {
            this.openShiftModal(match);
        }
    }

    findEventFromElement(eventEl) {
        const fcSeg = eventEl.fcSeg;
        if (fcSeg?.eventRange?.def?.publicId) {
            return this.calendar.getEventById(fcSeg.eventRange.def.publicId);
        }
        return null;
    }

    navigateToNewFromRange(startDate, endDate) {
        if (this.isNavigating || Swal.isVisible() || !this.hasNewUrlValue) return;
        if (!this.isSafeInternalUrl(this.newUrlValue)) return;

        this.isNavigating = true;
        const jsDay = startDate.getDay();
        const isoDay = jsDay === 0 ? 7 : jsDay;
        const pad = (n) => String(n).padStart(2, '0');
        const startStr = `${pad(startDate.getHours())}:${pad(startDate.getMinutes())}`;

        let endStr;
        if (endDate && endDate.toDateString() === startDate.toDateString()) {
            endStr = `${pad(endDate.getHours())}:${pad(endDate.getMinutes())}`;
        } else if (endDate && endDate.getHours() === 0 && endDate.getMinutes() === 0) {
            endStr = '23:59';
        } else {
            const fallbackEnd = new Date(startDate.getTime() + 60 * 60 * 1000);
            endStr = fallbackEnd.toDateString() === startDate.toDateString()
                ? `${pad(fallbackEnd.getHours())}:${pad(fallbackEnd.getMinutes())}`
                : '23:59';
        }

        const targetUrl = new URL(this.newUrlValue, window.location.origin);
        targetUrl.searchParams.set('day', String(isoDay));
        targetUrl.searchParams.set('start', startStr);
        targetUrl.searchParams.set('end', endStr);
        window.location.assign(targetUrl.pathname + targetUrl.search);
    }

    openShiftModal(fcEvent) {
        const props = fcEvent.extendedProps || {};
        const root = this.buildShiftModalContent(props);

        Swal.fire({
            html: root,
            showConfirmButton: false,
            showCancelButton: true,
            cancelButtonText: 'Cerrar',
            showCloseButton: true,
            returnFocus: true,
            width: '28rem',
            customClass: {
                popup: 'apple-swal-popup'
            }
        });
    }

    buildShiftModalContent(props) {
        const container = this.createEl('div', 'apple-doctor-modal apple-turno-modal');

        // Cabecera con avatar + médico + estado + chip de especialidad
        const header = this.createEl('div', 'apple-doctor-modal__header');
        header.appendChild(this.buildAvatar(props));
        header.appendChild(this.createEl('h3', 'apple-doctor-modal__name', props.doctorName || 'Médico'));

        const rolesWrap = this.createEl('div', 'apple-doctor-modal__roles');
        const espPill = this.createEl('span', 'apple-role-pill text-bg-primary', props.especialidadName || 'Especialidad');
        if (props.colorHex && /^#[0-9a-f]{6}$/i.test(props.colorHex)) {
            espPill.style.setProperty('--apple-esp-accent', props.colorHex);
        }
        rolesWrap.appendChild(espPill);

        const statusPill = this.createEl(
            'span',
            `apple-role-pill ${props.isActive ? 'text-bg-success' : 'text-bg-secondary'}`,
            props.isActive ? 'Turno Activo' : 'Turno Inactivo'
        );
        rolesWrap.appendChild(statusPill);
        header.appendChild(rolesWrap);
        container.appendChild(header);

        // Detalle agrupado tipo iOS
        const list = this.createEl('dl', 'apple-doctor-modal__list');
        const locationText = props.consultorioUbicacion
            ? `${props.consultorioName} · ${props.consultorioUbicacion}`
            : (props.consultorioName || 'Sin consultorio');

        list.appendChild(this.buildModalRow('bi-door-open-fill', 'Consultorio', locationText));
        list.appendChild(this.buildModalRow(
            'bi-clock-fill',
            'Horario Semanal',
            `${props.dayLabel || '—'} · ${props.startFormatted || ''} – ${props.endFormatted || ''} (${props.durationLabel || '—'})`
        ));
        container.appendChild(list);

        // Acciones rápidas
        const actions = this.createEl('div', 'apple-turno-modal__actions');

        if (this.isSafeInternalUrl(props.editUrl)) {
            const editLink = this.createEl('a', 'btn btn-primary apple-btn-primary rounded-pill flex-fill d-inline-flex align-items-center justify-content-center gap-2');
            editLink.href = props.editUrl;
            editLink.appendChild(this.createIcon('bi-pencil-square'));
            editLink.appendChild(this.createEl('span', '', 'Editar turno'));
            actions.appendChild(editLink);
        }

        if (this.isSafeInternalUrl(props.showUrl)) {
            const showLink = this.createEl('a', 'btn btn-secondary apple-btn-secondary rounded-pill d-inline-flex align-items-center justify-content-center gap-2');
            showLink.href = props.showUrl;
            showLink.appendChild(this.createIcon('bi-eye-fill'));
            showLink.appendChild(this.createEl('span', '', 'Detalle'));
            actions.appendChild(showLink);
        }

        if (this.isSafeInternalUrl(props.toggleUrl) && props.toggleToken) {
            const toggleBtn = this.createEl(
                'button',
                `btn rounded-pill d-inline-flex align-items-center justify-content-center gap-2 ${props.isActive ? 'apple-btn-warning' : 'btn-outline-success'}`
            );
            toggleBtn.type = 'button';
            toggleBtn.appendChild(this.createIcon('bi-power'));
            toggleBtn.appendChild(this.createEl('span', '', props.isActive ? 'Desactivar' : 'Activar'));
            toggleBtn.addEventListener('click', () => {
                this.confirmAndSubmit(props.toggleUrl, props.toggleToken, {
                    title: props.isActive ? '¿Desactivar este turno?' : '¿Activar este turno?',
                    text: props.isActive
                        ? 'El turno dejará de mostrarse como activo hasta reactivarlo.'
                        : 'Se comprobará que no se solape con otros turnos activos.',
                    icon: props.isActive ? 'warning' : 'question',
                    confirmButtonText: props.isActive ? 'Sí, desactivar' : 'Sí, activar'
                });
            });
            actions.appendChild(toggleBtn);
        }

        if (!props.isActive && this.isSafeInternalUrl(props.deleteUrl) && props.deleteToken) {
            const deleteBtn = this.createEl(
                'button',
                'btn btn-outline-danger apple-btn-danger rounded-pill d-inline-flex align-items-center justify-content-center gap-2'
            );
            deleteBtn.type = 'button';
            deleteBtn.appendChild(this.createIcon('bi-trash3-fill'));
            deleteBtn.appendChild(this.createEl('span', '', 'Eliminar'));
            deleteBtn.addEventListener('click', () => {
                this.confirmAndSubmit(props.deleteUrl, props.deleteToken, {
                    title: '¿Eliminar este turno?',
                    text: 'El turno inactivo se enviará a la papelera de forma segura.',
                    icon: 'error',
                    confirmButtonText: 'Sí, eliminar'
                });
            });
            actions.appendChild(deleteBtn);
        }

        container.appendChild(actions);
        return container;
    }

    buildAvatar(props) {
        const avatar = this.createEl('div', 'apple-doctor-avatar apple-doctor-avatar--lg');
        const initials = this.createEl('span', 'apple-doctor-avatar__initials', props.doctorInitials || 'DR');
        initials.setAttribute('aria-hidden', 'true');

        const avatarUrl = props.doctorId ? this.avatarsValue[`d_${props.doctorId}`] : '';
        if (avatarUrl) {
            const img = document.createElement('img');
            img.className = 'apple-doctor-avatar__img';
            img.alt = props.doctorName || 'Médico';
            img.src = avatarUrl;
            img.addEventListener('error', () => {
                img.remove();
                avatar.appendChild(initials);
            }, { once: true });
            avatar.appendChild(img);
        } else {
            avatar.appendChild(initials);
        }
        return avatar;
    }

    buildModalRow(iconClass, label, value) {
        const row = this.createEl('div', 'apple-doctor-modal__row');
        const iconWrap = this.createEl('span', 'apple-doctor-modal__row-icon');
        iconWrap.setAttribute('aria-hidden', 'true');
        iconWrap.appendChild(this.createIcon(iconClass));

        const body = this.createEl('div', 'apple-doctor-modal__row-body');
        body.appendChild(this.createEl('dt', 'apple-doctor-modal__dt', label));
        body.appendChild(this.createEl('dd', 'apple-doctor-modal__dd', value));

        row.appendChild(iconWrap);
        row.appendChild(body);
        return row;
    }

    confirmAndSubmit(actionUrl, token, cfg) {
        if (!this.isSafeInternalUrl(actionUrl)) return;

        Swal.fire({
            title: cfg.title,
            text: cfg.text,
            icon: cfg.icon,
            showCancelButton: true,
            confirmButtonText: cfg.confirmButtonText,
            cancelButtonText: 'Cancelar',
            customClass: { popup: 'apple-swal-popup' }
        }).then((result) => {
            if (!result.isConfirmed) return;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = actionUrl;
            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = token;
            form.appendChild(tokenInput);
            document.body.appendChild(form);
            form.submit();
        });
    }

    isSafeInternalUrl(url) {
        return typeof url === 'string' && url.startsWith('/') && !url.startsWith('//');
    }

    createEl(tag, className = '', text = '') {
        const el = document.createElement(tag);
        if (className) el.className = className;
        if (text) el.textContent = text;
        return el;
    }

    createIcon(biClass) {
        const icon = document.createElement('i');
        icon.className = `bi ${biClass}`;
        icon.setAttribute('aria-hidden', 'true');
        return icon;
    }
}
