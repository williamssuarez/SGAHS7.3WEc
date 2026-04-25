// assets/controllers/stock_adjuster_controller.js
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = { currentStock: Number }
    static targets = ['input', 'preview', 'submitBtn']

    connect() {
        this.calculate();
    }

    calculate() {
        // Allow negative numbers (e.g., -5 for breakages, +5 for found stock)
        const adjustment = parseInt(this.inputTarget.value) || 0;
        const projectedTotal = this.currentStockValue + adjustment;

        // Reset styling
        this.previewTarget.classList.remove('text-success', 'text-danger', 'text-warning', 'text-dark');

        if (adjustment === 0) {
            this.previewTarget.innerHTML = `${this.currentStockValue} <small class="text-muted">(Sin cambios)</small>`;
            this.previewTarget.classList.add('text-dark');
            this.submitBtnTarget.disabled = true;
            return;
        }

        if (projectedTotal < 0) {
            this.previewTarget.innerHTML = `<i class="bi bi-x-circle-fill"></i> Error: El stock no puede ser negativo (${projectedTotal})`;
            this.previewTarget.classList.add('text-danger');
            this.submitBtnTarget.disabled = true;
        } else if (adjustment < 0) {
            this.previewTarget.innerHTML = `${projectedTotal} <small class="text-muted"><i class="bi bi-arrow-down-right"></i> Sustracción</small>`;
            this.previewTarget.classList.add('text-warning');
            this.submitBtnTarget.disabled = false;
        } else {
            this.previewTarget.innerHTML = `${projectedTotal} <small class="text-muted"><i class="bi bi-arrow-up-right"></i> Adición</small>`;
            this.previewTarget.classList.add('text-success');
            this.submitBtnTarget.disabled = false;
        }
    }
}
