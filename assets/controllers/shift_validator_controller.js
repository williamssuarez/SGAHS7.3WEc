import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'dayOfWeek', 'startTime', 'endTime', 'consultorio', 'doctor',
        'feedbackContainer', 'feedbackText', 'submitButton'
    ]
    static values = {
        excludeId: Number,
        apiUrl: String
    }

    connect() {
        // Ejecutar validación inicial por si estamos en modo edición
        this.calculate();

        // Escuchar eventos de cambio de Select2 (que usa jQuery y detiene la propagación nativa)
        if (typeof $ !== 'undefined') {
            $(this.element).find('select').on('change', () => {
                this.calculate();
            });
        }
    }

    calculate() {
        const dayOfWeek = this.hasDayOfWeekTarget ? this.dayOfWeekTarget.value : null;
        const startTime = this.hasStartTimeTarget ? this.startTimeTarget.value : null;
        const endTime = this.hasEndTimeTarget ? this.endTimeTarget.value : null;
        const consultorio = this.hasConsultorioTarget ? this.consultorioTarget.value : null;
        const doctor = this.hasDoctorTarget ? this.doctorTarget.value : null;

        // Limpiar la UI mientras se obtienen datos completos
        if (!dayOfWeek || !startTime || !endTime || !consultorio || !doctor) {
            this.hideFeedback();
            // Deshabilitar botón hasta que todos los campos requeridos estén listos
            this.disableButton();
            return;
        }

        // Validación 1: Hora inicio vs Hora fin en el lado del cliente
        // Como startTime y endTime vienen en formato "HH:MM", una comparación de strings alfanumérica funciona perfecto.
        if (startTime >= endTime) {
            this.showFeedback('La hora de inicio debe ser estrictamente anterior a la hora de cierre.', 'danger');
            this.disableButton();
            return;
        }

        // Validación 2: Chequeo de cruces en el servidor
        this.checkConflicts(dayOfWeek, startTime, endTime, consultorio, doctor);
    }

    async checkConflicts(dayOfWeek, startTime, endTime, consultorio, doctor) {
        const payload = {
            dayOfWeek: dayOfWeek,
            startTime: startTime,
            endTime: endTime,
            consultorio: consultorio,
            doctor: doctor,
            excludeId: this.excludeIdValue || null
        };

        try {
            const response = await fetch(this.apiUrlValue, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            });

            if (response.ok) {
                const data = await response.json();
                
                if (data.valid) {
                    this.showFeedback(data.message, 'success');
                    this.enableButton();
                } else {
                    this.showFeedback(data.message, 'danger');
                    this.disableButton();
                }
            } else {
                this.showFeedback('Ocurrió un error al validar los datos con el servidor.', 'warning');
                this.disableButton();
            }
        } catch (error) {
            console.error('Error validating shift:', error);
            this.showFeedback('Ocurrió un error de conexión al validar.', 'warning');
            this.disableButton();
        }
    }

    showFeedback(message, type) {
        if (!this.hasFeedbackContainerTarget || !this.hasFeedbackTextTarget) return;

        const container = this.feedbackContainerTarget;
        
        // Reset classes
        container.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-warning');
        container.classList.add(`alert-${type}`);
        
        this.feedbackTextTarget.innerHTML = `<strong>Atención:</strong> ${message}`;
    }

    hideFeedback() {
        if (this.hasFeedbackContainerTarget) {
            this.feedbackContainerTarget.classList.add('d-none');
        }
    }

    disableButton() {
        if (this.hasSubmitButtonTarget) {
            this.submitButtonTarget.disabled = true;
        }
    }

    enableButton() {
        if (this.hasSubmitButtonTarget) {
            this.submitButtonTarget.disabled = false;
        }
    }
}
