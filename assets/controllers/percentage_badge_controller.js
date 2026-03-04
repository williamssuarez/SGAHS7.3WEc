import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["input", "badge"]

    connect() {
        this.update(); // Initialize on load
    }

    update() {
        const val = parseInt(this.inputTarget.value) || 0;
        this.badgeTarget.innerText = `${val}%`;

        // Reset classes
        this.badgeTarget.classList.remove('bg-success', 'bg-warning', 'bg-danger');

        // Apply medical "severity" colors
        if (val <= 30) {
            this.badgeTarget.classList.add('bg-success'); // Mild
            this.badgeTarget.dataset.severity = "Leve";
        } else if (val <= 60) {
            this.badgeTarget.classList.add('bg-warning', 'text-dark'); // Moderate
            this.badgeTarget.dataset.severity = "Moderada";
        } else {
            this.badgeTarget.classList.add('bg-danger'); // Severe
            this.badgeTarget.dataset.severity = "Severa";
        }
    }
}
