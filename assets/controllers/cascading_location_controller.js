import { Controller } from '@hotwired/stimulus';
import $ from 'jquery';

export default class extends Controller {
    static targets = ['estado', 'municipio', 'parroquia', 'sector'];

    connect() {
        $(this.estadoTarget).on('select2:select', (e) => e.target.dispatchEvent(new Event('change')));
        $(this.municipioTarget).on('select2:select', (e) => e.target.dispatchEvent(new Event('change')));
        $(this.parroquiaTarget).on('select2:select', (e) => e.target.dispatchEvent(new Event('change')));

        // Ensure proper initial placeholders if editing an existing record is not yet implemented
        if (!this.estadoTarget.value) {
            this.disableAndReset(this.municipioTarget, 'Seleccione un Estado primero');
            this.disableAndReset(this.parroquiaTarget, 'Seleccione un Municipio primero');
            this.disableAndReset(this.sectorTarget, 'Seleccione una Parroquia primero');
        }
    }

    async updateMunicipios(event) {
        const estadoId = event.target.value;
        this.disableAndReset(this.parroquiaTarget, 'Seleccione un Municipio primero');
        this.disableAndReset(this.sectorTarget, 'Seleccione una Parroquia primero');

        if (!estadoId) {
            this.disableAndReset(this.municipioTarget, 'Seleccione un Estado primero');
            return;
        }

        await this.fetchOptions(`/api/location/municipios/${estadoId}`, this.municipioTarget, 'Cargando municipios...');
    }

    async updateParroquias(event) {
        const municipioId = event.target.value;
        this.disableAndReset(this.sectorTarget, 'Seleccione una Parroquia primero');

        if (!municipioId) {
            this.disableAndReset(this.parroquiaTarget, 'Seleccione un Municipio primero');
            return;
        }

        await this.fetchOptions(`/api/location/parroquias/${municipioId}`, this.parroquiaTarget, 'Cargando parroquias...');
    }

    async updateSectores(event) {
        const parroquiaId = event.target.value;

        if (!parroquiaId) {
            this.disableAndReset(this.sectorTarget, 'Seleccione una Parroquia primero');
            return;
        }

        await this.fetchOptions(`/api/location/sectores/${parroquiaId}`, this.sectorTarget, 'Cargando sectores...');
    }

    async fetchOptions(url, targetSelect, loadingMessage) {
        this.setLoading(targetSelect, loadingMessage);

        try {
            const response = await fetch(url);
            const items = await response.json();

            targetSelect.innerHTML = '';
            targetSelect.add(new Option('Seleccione una opción', '', true, true));

            items.forEach(item => {
                targetSelect.add(new Option(item.nombre, item.id));
            });

            targetSelect.disabled = false;
            $(targetSelect).trigger('change');
        } catch (error) {
            this.disableAndReset(targetSelect, 'Error de conexión');
        }
    }

    disableAndReset(selectElement, placeholder) {
        selectElement.innerHTML = '';
        selectElement.add(new Option(placeholder, '', true, true));
        selectElement.disabled = true;
        $(selectElement).trigger('change');
    }

    setLoading(selectElement, message) {
        selectElement.innerHTML = '';
        selectElement.add(new Option(message, '', true, true));
        selectElement.disabled = true;
        $(selectElement).trigger('change');
    }
}
