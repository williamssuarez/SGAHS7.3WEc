// assets/controllers/margin_calculator_controller.js
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['compra', 'venta', 'feedback'];

    connect() {
        this.calculate(); // Run once on load
    }

    calculate() {
        const compra = parseFloat(this.compraTarget.value) || 0;
        const venta = parseFloat(this.ventaTarget.value) || 0;

        if (compra <= 0) {
            this.feedbackTarget.innerHTML = '<span class="text-muted">Ingrese costo para calcular margen</span>';
            return;
        }

        const margin = ((venta - compra) / compra) * 100;

        let colorClass = 'text-success';
        let icon = '<i class="bi bi-arrow-up-right"></i>';

        if (margin < 0) {
            colorClass = 'text-danger';
            icon = '<i class="bi bi-arrow-down-right"></i>';
        } else if (margin === 0) {
            colorClass = 'text-warning';
            icon = '<i class="bi bi-dash"></i>';
        }

        this.feedbackTarget.innerHTML = `<span class="fw-bold ${colorClass}">${icon} Margen: ${margin.toFixed(2)}%</span>`;
    }
}
