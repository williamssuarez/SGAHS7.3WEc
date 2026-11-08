import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'icon'];

    toggle() {
        if (!this.hasInputTarget) return;

        const isPassword = this.inputTarget.type === 'password';
        this.inputTarget.type = isPassword ? 'text' : 'password';

        if (this.hasIconTarget) {
            this.iconTarget.className = isPassword ? 'bi bi-eye-slash-fill' : 'bi bi-eye-fill';
        }
    }
}
