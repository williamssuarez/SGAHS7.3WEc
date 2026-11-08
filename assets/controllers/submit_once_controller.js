import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["submitButton"];
    static values = {
        text: { type: String, default: 'Guardando...' }
    };

    disable(event) {
        // We use a tiny timeout to ensure the form actually triggers
        // before the button is disabled (sometimes browsers cancel the submit if disabled too fast)
        setTimeout(() => {
            if (this.hasSubmitButtonTarget) {
                this.submitButtonTarget.disabled = true;
                this.submitButtonTarget.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>${this.textValue}`;
            }
        }, 0);
    }
}
