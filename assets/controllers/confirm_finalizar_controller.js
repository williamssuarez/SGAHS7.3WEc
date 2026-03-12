import { Controller } from '@hotwired/stimulus';
import Swal from 'sweetalert2';

export default class extends Controller {
    static values = {
        title: String,
        text: String,
        icon: String,
        confirmButtonText: String,
        cancelButtonText: String
    }

    confirm(event) {
        // Stop the form from submitting immediately
        event.preventDefault();

        Swal.fire({
            title: this.titleValue || '¿Estás seguro?',
            text: this.textValue || 'Una vez cerrada, no podrás modificar los datos de esta consulta.',
            icon: this.iconValue || 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: this.confirmButtonTextValue || 'Sí, finalizar',
            cancelButtonText: this.cancelButtonTextValue || 'Cancelar',
            reverseButtons: false
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Cargando...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen() {
                        Swal.showLoading();
                    }
                })
                // If confirmed, submit the form manually
                this.element.submit();
            }
        });
    }
}
