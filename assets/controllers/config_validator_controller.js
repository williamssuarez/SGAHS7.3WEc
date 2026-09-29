import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'especialidad', 'duracionCita', 'tiempoReceso', 'maxPacientesDia',
        'feedbackContainer', 'feedbackText'
    ]
    static values = {
        apiUrl: String
    }

    connect() {
        this.calculate();

        if (typeof $ !== 'undefined') {
            $(this.element).find('select').on('change', () => {
                this.calculate();
            });
        }
    }

    async calculate() {
        const especialidad = this.hasEspecialidadTarget ? this.especialidadTarget.value : null;
        const duracionCita = this.hasDuracionCitaTarget ? parseInt(this.duracionCitaTarget.value) : null;
        let tiempoReceso = this.hasTiempoRecesoTarget ? parseInt(this.tiempoRecesoTarget.value) : 0;
        const maxPacientesDia = this.hasMaxPacientesDiaTarget ? parseInt(this.maxPacientesDiaTarget.value) : null;

        if (isNaN(tiempoReceso)) tiempoReceso = 0;

        if (!especialidad || !duracionCita || isNaN(duracionCita) || isNaN(maxPacientesDia)) {
            this.hideFeedback();
            return;
        }

        const payload = {
            especialidad: especialidad,
            duracionCita: duracionCita,
            tiempoReceso: tiempoReceso
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
                    if (data.maxCapacity === 0) {
                        this.showFeedback('No hay médicos activos configurados para esta especialidad. Nadie podrá ser asignado.', 'warning');
                    } else if (maxPacientesDia > data.maxCapacity) {
                        this.showFeedback(`Atención: La capacidad física máxima de los doctores actualmente configurados para esta especialidad es de <strong>${data.maxCapacity} pacientes por día</strong> (basado en el día con más turnos). Un límite de ${maxPacientesDia} pacientes diarios causará embotellamientos en la lista de espera.`, 'warning');
                    } else {
                        // Todo bien, se oculta el feedback o se muestra verde
                        this.hideFeedback();
                    }
                } else {
                    this.showFeedback(data.message, 'danger');
                }
            } else {
                this.showFeedback('Ocurrió un error al validar la capacidad con el servidor.', 'danger');
            }
        } catch (error) {
            console.error('Error validating capacity:', error);
            this.showFeedback('Ocurrió un error de conexión al validar.', 'danger');
        }
    }

    showFeedback(message, type) {
        if (!this.hasFeedbackContainerTarget || !this.hasFeedbackTextTarget) return;

        const container = this.feedbackContainerTarget;
        
        container.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-warning');
        container.classList.add(`alert-${type}`);
        
        this.feedbackTextTarget.innerHTML = `<strong>Aviso:</strong> ${message}`;
    }

    hideFeedback() {
        if (this.hasFeedbackContainerTarget) {
            this.feedbackContainerTarget.classList.add('d-none');
        }
    }
}
