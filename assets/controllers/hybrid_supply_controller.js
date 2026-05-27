// assets/controllers/hybrid_supply_controller.js
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['toggle', 'hospitalSection', 'patientSection', 'hospitalInput', 'patientInput']

    connect() {
        // Run once on load to ensure the correct fields are showing
        this.toggleFields();
    }

    toggleFields() {
        if (this.toggleTarget.checked) {
            // PATIENT BROUGHT IT
            this.hospitalSectionTarget.classList.add('d-none');
            this.patientSectionTarget.classList.remove('d-none');

            // Clear the select2 hospital input so we don't save a ghost item
            if (window.jQuery && $(this.hospitalInputTarget).data('select2')) {
                $(this.hospitalInputTarget).val(null).trigger('change');
            } else {
                this.hospitalInputTarget.value = '';
            }

            // Optionally auto-focus the text field
            this.patientInputTarget.focus();

        } else {
            // HOSPITAL PROVIDED IT
            this.patientSectionTarget.classList.add('d-none');
            this.hospitalSectionTarget.classList.remove('d-none');

            // Clear the patient text input
            this.patientInputTarget.value = '';
        }
    }
}
