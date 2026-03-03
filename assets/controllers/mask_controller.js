import { Controller } from '@hotwired/stimulus';
import Inputmask from "inputmask";

export default class extends Controller {
    static values = {
        type: String // We can pass 'decimal', 'integer', etc.
    }

    connect() {
        const options = {};

        if (this.typeValue === 'decimal') {
            options.alias = 'decimal';
            options.groupSeparator = '';
            options.autoGroup = true;
            options.digits = 2;
            options.digitsOptional = false;
            options.placeholder = '0';
            options.rightAlign = false; // Usually better for forms
        }

        Inputmask(options).mask(this.element);
    }
}
