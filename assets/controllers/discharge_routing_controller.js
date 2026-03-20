import { Controller } from '@hotwired/stimulus';
import $ from 'jquery';

export default class extends Controller {
    static targets = ["condition", "transferSection", "admissionSection", "instructionsSection"]

    connect() {
        this.toggleFields();

        // Listen for Select2 changes if applied to the condition dropdown
        $(this.conditionTarget).on('change', () => {
            this.toggleFields();
        });
    }

    toggleFields() {
        const condition = this.conditionTarget.value;

        // Reset all dynamic sections first
        this.transferSectionTarget.classList.add('d-none');
        this.admissionSectionTarget.classList.add('d-none');
        this.instructionsSectionTarget.classList.remove('d-none'); // Usually visible

        // Reveal based on selection
        if (condition === 'transfer') {
            this.transferSectionTarget.classList.remove('d-none');
            this.instructionsSectionTarget.classList.add('d-none'); // Don't need home instructions if transferring
        }
        else if (condition === 'admitted_room') {
            this.admissionSectionTarget.classList.remove('d-none');
            this.instructionsSectionTarget.classList.add('d-none'); // Inpatient orders are handled elsewhere
        }
        else if (condition === 'deceased') {
            this.instructionsSectionTarget.classList.add('d-none');
        }
    }
}
