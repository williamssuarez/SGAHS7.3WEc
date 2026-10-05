import { Controller } from '@hotwired/stimulus';
import Swal from 'sweetalert2';

export default class extends Controller {
    static values = {
        title: String,
        text: String,
        icon: { type: String, default: 'warning' },
        confirmText: { type: String, default: 'Sí, continuar' },
        cancelText: { type: String, default: 'Cancelar' }
    }

    confirm(event) {
        event.preventDefault();

        Swal.fire({
            title: this.hasTitleValue ? this.titleValue : '¿Estás seguro?',
            text: this.hasTextValue ? this.textValue : 'Esta acción no se puede deshacer.',
            icon: this.iconValue,
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: this.confirmTextValue,
            cancelButtonText: this.cancelTextValue
        }).then((result) => {
            if (result.isConfirmed) {
                // To allow the form to submit WITH the button's name/value, we append a hidden input
                const form = this.element.closest('form');
                if (form) {
                    let appendedInput = null;
                    if (this.element.name && this.element.value) {
                        appendedInput = document.createElement('input');
                        appendedInput.type = 'hidden';
                        appendedInput.name = this.element.name;
                        appendedInput.value = this.element.value;
                        form.appendChild(appendedInput);
                    }
                    
                    const originalTarget = form.target;
                    if (this.element.getAttribute('formtarget')) {
                        form.target = this.element.getAttribute('formtarget');
                    }
                    
                    form.submit();
                    
                    setTimeout(() => {
                        if (appendedInput) {
                            form.removeChild(appendedInput);
                        }
                        if (this.element.getAttribute('formtarget')) {
                            form.target = originalTarget;
                        }
                    }, 100);
                } else if (this.element.tagName.toLowerCase() === 'a') {
                    window.location.href = this.element.href;
                }
            }
        });
    }
}
