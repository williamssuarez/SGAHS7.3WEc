// assets/controllers/surgery_state_controller.js
import { Controller } from '@hotwired/stimulus';
import Swal from 'sweetalert2';

export default class extends Controller {
    static values = { id: Number }

    async advance(event) {
        const button = event.currentTarget;
        const nextState = button.dataset.nextState;

        let isConfirmed = false;
        let isAdmitir = false;

        if (nextState === 'finalizada') {
            const result = await Swal.fire({
                title: '¿Confirmar Finalización?',
                text: 'La cirugía ha concluido. ¿El paciente será dado de alta o pasará a hospitalización?',
                icon: 'question',
                showDenyButton: true,
                showCancelButton: true,
                confirmButtonText: '<i class="bi bi-house"></i> Alta Ambulatoria',
                denyButtonText: '<i class="bi bi-hospital"></i> Trasladar a Planta',
                cancelButtonText: 'Cancelar'
            });
            
            if (result.isConfirmed) {
                isConfirmed = true;
            } else if (result.isDenied) {
                isConfirmed = true;
                isAdmitir = true;
            }
        } else {
            const result = await Swal.fire({
                title: '¿Confirmar avance?',
                text: 'Se registrará la hora actual en el expediente quirúrgico.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, registrar hora',
                cancelButtonText: 'Cancelar'
            });
            isConfirmed = result.isConfirmed;
        }

        if (isConfirmed) {
            // Disable button to prevent double clicks
            button.dataset.originalHtml = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Procesando...';

            try {
                const response = await fetch(`/cirugia/${this.idValue}/avanzar-estado`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ 
                        estado: nextState,
                        admitir_hospitalizacion: isAdmitir 
                    })
                });

                const responseData = await response.json();

                if (response.ok) {
                    if (responseData.redirect_url) {
                        window.location.href = responseData.redirect_url;
                    } else {
                        // Success! Reload the page to reflect the new state in the Grid
                        window.location.reload();
                    }
                } else {
                    Swal.fire('Error', responseData.message || 'No se pudo actualizar el estado.', 'error');
                    button.disabled = false;
                    button.innerHTML = button.dataset.originalHtml || 'Intentar de nuevo';
                }
            } catch (error) {
                Swal.fire('Error', 'Error de conexión.', 'error');
                button.disabled = false;
                button.innerHTML = button.dataset.originalHtml || 'Intentar de nuevo';
            }
        }
    }
}
