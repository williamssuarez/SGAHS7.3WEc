import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [ "peso", "altura", "result" ]

    connect() {
        // Run calculation once on load in case there's already data (like in an Edit form)
        this.calculate();
    }

    calculate() {
        const peso = parseFloat(this.pesoTarget.value);
        const altura = parseFloat(this.alturaTarget.value);

        if (peso > 0 && altura > 0) {
            // BMI Formula: kg / (m * m)
            const alturaMeters = altura / 100;
            const imc = peso / (alturaMeters * alturaMeters);

            // Limit to 2 decimal places
            this.resultTarget.value = imc.toFixed(2);

            // Bonus: trigger a custom event if you want other parts of the UI to react
            this.dispatch("updated", { detail: { imc: imc } });
        } else {
            this.resultTarget.value = '';
        }
    }
}
