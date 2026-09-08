import { Controller } from '@hotwired/stimulus';
import $ from 'jquery';

export default class extends Controller {
    static targets = ['rolesSelect', 'especialidadesContainer'];

    connect() {
        $(this.rolesSelectTarget).on('select2:select select2:unselect', () => {
            this.toggleEspecialidades();
        });

        this.toggleEspecialidades();
    }

    toggleEspecialidades() {
        const selectedRoles = $(this.rolesSelectTarget).val() || [];

        const isDoctor = selectedRoles.includes('ROLE_DOCTOR') ||
            selectedRoles.includes('ROLE_ER_DOCTOR') ||
            selectedRoles.includes('ROLE_DOCTOR_QUIROFANO');

        const especialidadesSelect = this.especialidadesContainerTarget.querySelector('select');

        if (isDoctor) {
            this.especialidadesContainerTarget.style.display = 'block';

            if (especialidadesSelect) {
                especialidadesSelect.setAttribute('required', 'required');
            }
        } else {
            this.especialidadesContainerTarget.style.display = 'none';

            if (especialidadesSelect) {
                especialidadesSelect.removeAttribute('required');
                $(especialidadesSelect).val(null).trigger('change');
            }
        }
    }
}
