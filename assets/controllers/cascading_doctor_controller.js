import { Controller } from '@hotwired/stimulus';
import $ from 'jquery';

export default class extends Controller {
    static targets = ['especialidad', 'doctor'];

    connect() {
        // Trigger the cascade when the specialty changes via Select2
        $(this.especialidadTarget).on('select2:select', () => this.updateForm(this.especialidadTarget, this.doctorTarget, 'Cargando doctores...'));

        // Handle initial load state (for new records)
        if (!this.especialidadTarget.value) {
            this.disableAndReset(this.doctorTarget, 'Seleccione una Especialidad primero');
        }
    }

    async updateForm(sourceTarget, nextTarget, loadingMessage) {
        if (!sourceTarget.value) {
            this.disableAndReset(nextTarget, 'Selección inválida');
            return;
        }

        this.disableAndReset(nextTarget, loadingMessage);

        const form = sourceTarget.closest('form');
        const formData = new FormData(form);

        try {
            const response = await fetch(form.action || window.location.href, {
                method: form.getAttribute('method') || 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const newSelect = doc.getElementById(nextTarget.id);
            if (newSelect) {
                nextTarget.innerHTML = newSelect.innerHTML;
                nextTarget.disabled = false;
                $(nextTarget).trigger('change');
            }
        } catch (error) {
            this.disableAndReset(nextTarget, 'Error de conexión');
        }
    }

    disableAndReset(selectElement, placeholder) {
        selectElement.innerHTML = '';
        selectElement.add(new Option(placeholder, '', true, true));
        selectElement.disabled = true;
        $(selectElement).trigger('change');
    }
}
