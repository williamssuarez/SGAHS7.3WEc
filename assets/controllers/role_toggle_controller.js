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

        if (isDoctor) {
            this.especialidadesContainerTarget.style.display = 'block';
        } else {
            this.especialidadesContainerTarget.style.display = 'none';

            const especialidadesSelect = this.especialidadesContainerTarget.querySelector('select');
            if (especialidadesSelect) {
                $(especialidadesSelect).val(null).trigger('change');
            }
        }
    }
}
