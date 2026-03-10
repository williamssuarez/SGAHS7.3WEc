// assets/controllers/toggle_date_controller.js
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["state", "endDateContainer"]
    static values = {
        hiddenValue: { type: String, default: "active" }
    }

    connect() {
        this.toggle();
    }

    toggle() {
        let currentValue = "";

        // Support for both Select and Radio inputs
        if (this.stateTarget.tagName === 'SELECT') {
            currentValue = this.stateTarget.value;
        } else {
            const checkedRadio = this.stateTargets.find(radio => radio.checked);
            currentValue = checkedRadio ? checkedRadio.value : "";
        }

        // LOGIC: If the value is NOT the 'hiddenValue', show the container
        // Also ensure we don't show it if nothing is selected (currentValue === "")
        if (currentValue !== this.hiddenValueValue && currentValue !== "") {
            this.endDateContainerTarget.classList.remove('d-none');
        } else {
            this.endDateContainerTarget.classList.add('d-none');
        }
    }
}
