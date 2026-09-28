import { Controller } from '@hotwired/stimulus';
import Swal from 'sweetalert2';

export default class extends Controller {
    static values = {
        title: String,
        text: String,
        icon: String,
        confirmButtonText: String,
        cancelButtonText: String,
        loadingText: String
    }

    confirm(event) {
        event.preventDefault();

        Swal.fire({
            title: this.hasTitleValue ? this.titleValue : '¿Estás seguro?',
            text: this.hasTextValue ? this.textValue : 'Esta acción no se puede deshacer.',
            icon: this.hasIconValue ? this.iconValue : 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: this.hasConfirmButtonTextValue ? this.confirmButtonTextValue : 'Sí, continuar',
            cancelButtonText: this.hasCancelButtonTextValue ? this.cancelButtonTextValue : 'Cancelar',
            reverseButtons: false
        }).then((result) => {
            if (result.isConfirmed) {
                // Prevent multiple clicks by disabling the submit button inside the form (if any)
                const submitBtn = this.element.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                }

                Swal.fire({
                    title: this.hasLoadingTextValue ? this.loadingTextValue : 'Procesando...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen() {
                        Swal.showLoading();
                    }
                });
                
                this.element.submit();
            }
        });
    }
}
