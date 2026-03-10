import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["state", "endDateContainer"]

    connect() {
        console.log("Targets found:", this.stateTargets.length); // Should be 3 (active, resolved, chronic)
        this.toggle();
    }

    toggle() {
        let currentValue = "";

        if (this.stateTarget.tagName === 'SELECT') {
            currentValue = this.stateTarget.value;
        } else {
            const checkedRadio = this.stateTargets.find(radio => radio.checked);
            currentValue = checkedRadio ? checkedRadio.value : "";
        }

        if (currentValue === 'resolved') {
            this.endDateContainerTarget.classList.remove('d-none');
        } else {
            this.endDateContainerTarget.classList.add('d-none');
        }
    }
}
