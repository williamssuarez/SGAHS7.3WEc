import { Controller } from '@hotwired/stimulus';
import $ from 'jquery';

export default class extends Controller {
    static targets = [
        'dayOfWeek', 'startTime', 'endTime', 'consultorio', 'doctor',
        'feedbackContainer', 'feedbackIcon', 'feedbackText', 'durationBadge', 'submitButton'
    ];
    static values = {
        excludeId: Number,
        apiUrl: String
    };

    connect() {
        this.requestSeq = 0;
        this.abortController = null;

        // Mantener botón habilitado inicialmente para permitir resaltar errores si intentan enviar (modern-web-guidance: forms)
        this.enableButton();
        this.calculate();

        // Select2 dispara eventos jQuery sin propagación nativa DOM
        if (typeof $ !== 'undefined') {
            $(this.element).find('select').on('change.shiftValidator', () => {
                this.calculate();
            });
        }
    }

    disconnect() {
        this.abortController?.abort();
        if (typeof $ !== 'undefined') {
            $(this.element).find('select').off('change.shiftValidator');
        }
    }

    calculate() {
        const dayOfWeek = this.hasDayOfWeekTarget ? this.dayOfWeekTarget.value : null;
        const startTime = this.hasStartTimeTarget ? this.startTimeTarget.value : null;
        const endTime = this.hasEndTimeTarget ? this.endTimeTarget.value : null;
        const consultorio = this.hasConsultorioTarget ? this.consultorioTarget.value : null;
        const doctor = this.hasDoctorTarget ? this.doctorTarget.value : null;

        this.updateDurationBadge(startTime, endTime);

        // Si aún faltan campos, no bloquear ni mostrar errores prematuros
        if (!dayOfWeek || !startTime || !endTime || !consultorio || !doctor) {
            this.hideFeedback();
            this.clearTimeInvalidState();
            this.enableButton();
            return;
        }

        if (startTime >= endTime) {
            this.markTimeInvalid();
            this.showFeedback('La hora de inicio debe ser estrictamente anterior a la hora de cierre.', 'danger');
            this.enableButton();
            return;
        }

        this.clearTimeInvalidState();
        this.checkConflicts(dayOfWeek, startTime, endTime, consultorio, doctor);
    }

    updateDurationBadge(startTime, endTime) {
        if (!this.hasDurationBadgeTarget) return;

        const badge = this.durationBadgeTarget;
        badge.classList.remove('is-valid', 'is-invalid', 'is-empty');

        if (!startTime || !endTime) {
            badge.classList.add('is-empty');
            badge.textContent = 'Duración: selecciona inicio y cierre';
            return;
        }

        const startMin = this.parseMinutes(startTime);
        const endMin = this.parseMinutes(endTime);
        if (startMin === null || endMin === null) {
            badge.classList.add('is-empty');
            badge.textContent = 'Duración: --';
            return;
        }

        const diff = endMin - startMin;
        if (diff <= 0) {
            badge.classList.add('is-invalid');
            badge.textContent = 'Rango horario inválido';
            return;
        }

        const hours = Math.floor(diff / 60);
        const mins = diff % 60;
        const formatted = hours > 0 && mins > 0
            ? `${hours} h ${String(mins).padStart(2, '0')} min`
            : (hours > 0 ? `${hours} h` : `${mins} min`);

        badge.classList.add('is-valid');
        badge.textContent = `Bloque de ${formatted}`;
    }

    parseMinutes(value) {
        const match = /^([01]\d|2[0-3]):([0-5]\d)/.exec((value || '').trim());
        if (!match) return null;
        return Number(match[1]) * 60 + Number(match[2]);
    }

    async checkConflicts(dayOfWeek, startTime, endTime, consultorio, doctor) {
        const currentSeq = ++this.requestSeq;
        this.abortController?.abort();
        this.abortController = new AbortController();

        const payload = {
            dayOfWeek,
            startTime,
            endTime,
            consultorio,
            doctor,
            excludeId: this.excludeIdValue || null
        };

        try {
            const response = await fetch(this.apiUrlValue, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload),
                signal: this.abortController.signal
            });

            if (currentSeq !== this.requestSeq) return;

            if (response.ok) {
                const data = await response.json();
                if (data.valid) {
                    this.showFeedback(data.message, 'success');
                } else {
                    this.showFeedback(data.message, 'danger');
                }
            } else {
                this.showFeedback('No fue posible verificar conflictos en el servidor.', 'warning');
            }
        } catch (error) {
            if (error.name === 'AbortError' || currentSeq !== this.requestSeq) return;
            console.error('Error validating shift:', error);
            this.showFeedback('Error de conexión al comprobar disponibilidad horaria.', 'warning');
        } finally {
            if (currentSeq === this.requestSeq) {
                this.enableButton();
            }
        }
    }

    showFeedback(message, type) {
        if (!this.hasFeedbackContainerTarget || !this.hasFeedbackTextTarget) return;

        const container = this.feedbackContainerTarget;
        container.classList.remove('d-none', 'apple-alert-success', 'apple-alert-danger', 'apple-alert-warning');
        container.classList.add(`apple-alert-${type}`);

        if (this.hasFeedbackIconTarget) {
            const iconMap = {
                success: 'bi-check-circle-fill',
                danger: 'bi-exclamation-octagon-fill',
                warning: 'bi-exclamation-triangle-fill'
            };
            this.feedbackIconTarget.className = `bi ${iconMap[type] || 'bi-info-circle-fill'}`;
        }

        const prefix = type === 'success' ? 'Disponible: ' : (type === 'danger' ? 'Conflicto: ' : 'Atención: ');
        this.feedbackTextTarget.textContent = `${prefix}${message}`;
    }

    hideFeedback() {
        if (this.hasFeedbackContainerTarget) {
            this.feedbackContainerTarget.classList.add('d-none');
        }
    }

    markTimeInvalid() {
        if (!this.hasEndTimeTarget) return;
        this.endTimeTarget.setAttribute('aria-invalid', 'true');
        if (this.hasFeedbackContainerTarget && this.feedbackContainerTarget.id) {
            this.endTimeTarget.setAttribute('aria-describedby', this.feedbackContainerTarget.id);
        }
    }

    clearTimeInvalidState() {
        if (!this.hasEndTimeTarget) return;
        this.endTimeTarget.removeAttribute('aria-invalid');
        this.endTimeTarget.removeAttribute('aria-describedby');
    }

    enableButton() {
        if (this.hasSubmitButtonTarget) {
            this.submitButtonTarget.disabled = false;
        }
    }
}
