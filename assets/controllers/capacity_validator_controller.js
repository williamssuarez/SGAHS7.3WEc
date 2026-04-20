import { Controller } from '@hotwired/stimulus';
import $ from 'jquery'; // Needed strictly to bridge Select2 events

export default class extends Controller {
    static targets = [
        'inicio', 'fin', 'duracion', 'tieneReceso', 'receso',
        'consultorios', 'max', 'submitBtn', 'feedbackContainer', 'feedbackText'
    ]

    connect() {
        // Bridge Select2 changes to our calculation method
        $(this.consultoriosTarget).on('change', () => {
            this.calculate();
        });

        // Run once on load in case of editing an existing record
        this.calculate();
    }

    disconnect() {
        $(this.consultoriosTarget).off('change');
    }

    calculate() {
        const inicioVal = this.inicioTarget.value;
        const finVal = this.finTarget.value;
        const duracion = parseInt(this.duracionTarget.value) || 0;
        const maxPacientes = parseInt(this.maxTarget.value) || 0;

        // 1. Count selected consultorios (works for standard multiple selects)
        let consultoriosCount = 0;
        for (let i = 0; i < this.consultoriosTarget.options.length; i++) {
            if (this.consultoriosTarget.options[i].selected) consultoriosCount++;
        }

        // If core fields are missing, don't lock the form, let HTML5/Backend handle it
        if (!inicioVal || !finVal || duracion <= 0 || consultoriosCount === 0 || maxPacientes <= 0) {
            this.clearFeedback();
            return;
        }

        // Parse HH:mm to total minutes
        const inicioMins = this.timeToMins(inicioVal);
        const finMins = this.timeToMins(finVal);
        let totalMinutes = finMins - inicioMins;

        // Handle overnight shifts or invalid ranges safely
        if (totalMinutes <= 0) {
            totalMinutes += (24 * 60);
        }

        // 2. Determine Break Time (Receso)
        let receso = 0;
        if (this.tieneRecesoTarget.checked) {
            receso = parseInt(this.recesoTarget.value) || 0;
        }

        // 3. The Math
        const minsPerPatient = duracion + receso;
        const slotsPerOffice = Math.floor(totalMinutes / minsPerPatient);
        const totalCapacity = slotsPerOffice * consultoriosCount;

        // 4. The Validation
        if (maxPacientes > totalCapacity) {
            this.showFeedback(
                `Capacidad insuficiente. Incluyendo el receso, cada cita ocupa ${minsPerPatient} minutos. El límite real es de <strong>${totalCapacity} pacientes</strong>.`,
                'danger'
            );
            this.submitBtnTarget.disabled = true;
        } else {
            this.showFeedback(
                `Capacidad óptima. Esta configuración permite atender hasta <strong>${totalCapacity} pacientes</strong> en total.`,
                'success'
            );
            this.submitBtnTarget.disabled = false;
        }
    }

    timeToMins(timeStr) {
        // timeStr format comes from inputmask: 'hh:ii' -> e.g. "14:30"
        if (!timeStr.includes(':')) return 0;
        const [h, m] = timeStr.split(':');
        return (parseInt(h) * 60) + parseInt(m);
    }

    showFeedback(message, type) {
        this.feedbackContainerTarget.classList.remove('d-none', 'alert-danger', 'alert-success');
        this.feedbackContainerTarget.classList.add(`alert-${type}`);
        this.feedbackTextTarget.innerHTML = `<i class="bi bi-info-circle-fill"></i> ${message}`;
    }

    clearFeedback() {
        this.feedbackContainerTarget.classList.add('d-none');
        this.submitBtnTarget.disabled = false;
    }
}
