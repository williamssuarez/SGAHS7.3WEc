import { Controller } from '@hotwired/stimulus';
import $ from 'jquery'; // Don't forget to import jQuery if you are using Select2!

export default class extends Controller {
    static targets = ["priority", "consultationSection", "checkbox", "specialtySection"]

    connect() {
        // Run on initial load
        this.toggleSections();

        // THE FIX: Listen to the jQuery change event in case Select2 is active
        $(this.priorityTarget).on('change', () => {
            this.toggleSections();
        });
    }

    toggleSections() {
        const priority = this.priorityTarget.value;
        console.log('Prioridad seleccionada:', priority);

        // Double check your enum values! Sometimes they are uppercase depending on your Doctrine setup
        const isLowPriority = priority.toLowerCase() === 'level_4' || priority.toLowerCase() === 'level_5';

        if (isLowPriority) {
            this.consultationSectionTarget.classList.remove('d-none');
        } else {
            this.consultationSectionTarget.classList.add('d-none');
            // If the checkbox target is missing in the DOM, this next line will silently crash the JS!
            this.checkboxTarget.checked = false;
        }

        this.toggleSpecialty();
    }

    toggleSpecialty() {
        if (this.checkboxTarget.checked) {
            this.specialtySectionTarget.classList.remove('d-none');
        } else {
            this.specialtySectionTarget.classList.add('d-none');
        }
    }
}
