// assets/controllers/submit_once_controller.js
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["submitButton"]

    disable(event) {
        // We use a tiny timeout to ensure the form actually triggers
        // before the button is disabled (sometimes browsers cancel the submit if disabled too fast)
        setTimeout(() => {
            this.submitButtonTarget.disabled = true;
            this.submitButtonTarget.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...';
        }, 0);
    }
}
