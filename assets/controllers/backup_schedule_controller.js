import { Controller } from '@hotwired/stimulus';
import $ from 'jquery';

export default class extends Controller {
    static targets = ['frequency', 'dayOfWeekContainer', 'dayOfMonthContainer'];

    connect() {
        this.boundToggle = () => this.toggle();

        if (this.hasFrequencyTarget) {
            this.frequencyTarget.addEventListener('change', this.boundToggle);
            $(this.frequencyTarget).on('change select2:select select2:clear', this.boundToggle);
        }

        this.toggle();
    }

    disconnect() {
        if (this.hasFrequencyTarget) {
            this.frequencyTarget.removeEventListener('change', this.boundToggle);
            $(this.frequencyTarget).off('change select2:select select2:clear', this.boundToggle);
        }
    }

    toggle() {
        if (!this.hasFrequencyTarget) return;

        const val = $(this.frequencyTarget).val() || this.frequencyTarget.value;

        if (this.hasDayOfWeekContainerTarget) {
            if (val === 'weekly') {
                this.dayOfWeekContainerTarget.classList.remove('d-none');
            } else {
                this.dayOfWeekContainerTarget.classList.add('d-none');
            }
        }

        if (this.hasDayOfMonthContainerTarget) {
            if (val === 'monthly') {
                this.dayOfMonthContainerTarget.classList.remove('d-none');
            } else {
                this.dayOfMonthContainerTarget.classList.add('d-none');
            }
        }
    }
}
