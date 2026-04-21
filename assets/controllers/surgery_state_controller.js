// assets/controllers/surgery_state_controller.js
import { Controller } from '@hotwired/stimulus';
import Swal from 'sweetalert2';

export default class extends Controller {
    static values = { id: Number }

    async advance(event) {
        const button = event.currentTarget;
        const nextState = button.dataset.nextState;

        // Use your trusted SweetAlert pattern!
        const result = await Swal.fire({
            title: '¿Confirmar avance?',
            text: 'Se registrará la hora actual en el expediente quirúrgico.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, registrar hora',
            cancelButtonText: 'Cancelar'
        });

        if (result.isConfirmed) {
            // Disable button to prevent double clicks
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Procesando...';

            try {
                const response = await fetch(`/cirugia/${this.idValue}/avanzar-estado`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ estado: nextState })
                });

                if (response.ok) {
                    // Success! Reload the page to reflect the new state in the Grid
                    window.location.reload();
                } else {
                    Swal.fire('Error', 'No se pudo actualizar el estado.', 'error');
                    button.disabled = false;
                }
            } catch (error) {
                Swal.fire('Error', 'Error de conexión.', 'error');
                button.disabled = false;
            }
        }
    }
}
