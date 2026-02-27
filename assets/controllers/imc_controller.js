import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [ "peso", "altura", "result" ]

    connect() {
        // Calcular al cargar la pagina (en caso de que ya haya data precargada como en un edit)
        this.calculate();
    }

    calculate() {
        const peso = parseFloat(this.pesoTarget.value);
        const altura = parseFloat(this.alturaTarget.value);

        if (peso > 0 && altura > 0) {
            // BMI Formula: kg / (m * m)
            const alturaMeters = altura / 100;
            const imc = peso / (alturaMeters * alturaMeters);

            // Limite de 2 decimales
            this.resultTarget.value = imc.toFixed(2);

            // Bonus: trigger a custom event if you want other parts of the UI to react
            this.dispatch("updated", { detail: { imc: imc } });
        } else {
            this.resultTarget.value = '';
        }
    }
}
