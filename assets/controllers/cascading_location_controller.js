import { Controller } from '@hotwired/stimulus';
import $ from 'jquery';

export default class extends Controller {
    static targets = ['estado', 'municipio', 'parroquia', 'sector'];

    connect() {
        $(this.estadoTarget).on('select2:select', () => this.updateForm(this.estadoTarget, this.municipioTarget, 'Cargando municipios...'));
        $(this.municipioTarget).on('select2:select', () => this.updateForm(this.municipioTarget, this.parroquiaTarget, 'Cargando parroquias...'));
        $(this.parroquiaTarget).on('select2:select', () => this.updateForm(this.parroquiaTarget, this.sectorTarget, 'Cargando sectores...'));

        if (!this.estadoTarget.value) {
            this.disableAndReset(this.municipioTarget, 'Seleccione un Estado primero');
            this.disableAndReset(this.parroquiaTarget, 'Seleccione un Municipio primero');
            this.disableAndReset(this.sectorTarget, 'Seleccione una Parroquia primero');
        }
    }

    async updateForm(sourceTarget, nextTarget, loadingMessage) {
        // Cascade clearing depending on which parent was changed
        if (sourceTarget === this.estadoTarget) {
            this.disableAndReset(this.parroquiaTarget, 'Seleccione un Municipio primero');
            this.disableAndReset(this.sectorTarget, 'Seleccione una Parroquia primero');
        } else if (sourceTarget === this.municipioTarget) {
            this.disableAndReset(this.sectorTarget, 'Seleccione una Parroquia primero');
        }

        if (!sourceTarget.value) {
            this.disableAndReset(nextTarget, 'Selección inválida');
            return;
        }

        this.disableAndReset(nextTarget, loadingMessage);

        const form = sourceTarget.closest('form');
        const formData = new FormData(form);

        try {
            // Submit form to itself to let Symfony natively generate the updated fields
            const response = await fetch(form.action || window.location.href, {
                method: form.getAttribute('method') || 'POST',
                body: formData,
                credentials: 'same-origin', // Ensures the PHPSESSID cookie is sent
                headers: {
                    'X-Requested-With': 'XMLHttpRequest' // Tells Symfony this is an AJAX call
                }
            });

            // Even if response.ok is false (422 validation error), we still extract the HTML
            if (response.status === 422) {
                console.log("Validation or CSRF error occurred.");
            }
            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            // Find the updated dropdown in the returned HTML by its ID
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
