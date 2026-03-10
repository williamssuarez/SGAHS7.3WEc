// assets/controllers/medication_status_controller.js
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["state", "reasonContainer"]

    connect() {
        this.toggle();
    }

    toggle() {
        let currentValue = "";

        // Support for Radios
        const checkedRadio = this.stateTargets.find(radio => radio.checked);
        currentValue = checkedRadio ? checkedRadio.value : "";

        // Show only if 'suspendida'
        if (currentValue === 'suspendida') {
            this.reasonContainerTarget.classList.remove('d-none');
        } else {
            this.reasonContainerTarget.classList.add('d-none');
        }
    }
}
