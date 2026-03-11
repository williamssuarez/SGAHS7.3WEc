import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["trigger", "container"]

    connect() {
        this.toggle();
    }

    toggle() {
        // If 'trigger' is a checkbox, we check the .checked property
        const isChecked = this.triggerTarget.checked;

        if (isChecked) {
            this.containerTarget.classList.remove('d-none');
            // Optional: focus the input when it appears
            this.containerTarget.querySelector('input')?.focus();
        } else {
            this.containerTarget.classList.add('d-none');
            // Optional: clear the value if they uncheck it
            // this.containerTarget.querySelector('input').value = '';
        }
    }
}
